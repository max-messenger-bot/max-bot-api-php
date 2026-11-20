<?php

declare(strict_types=1);

namespace MaxMessenger\Bot;

use Closure;
use MaxMessenger\Bot\Contract\MaxApiConfigInterface;
use MaxMessenger\Bot\Exception\MaxBot\Event\EventException;
use MaxMessenger\Bot\Exception\Validation\MaxLengthException;
use MaxMessenger\Bot\Exception\Validation\MinLengthException;
use MaxMessenger\Bot\MaxBot\Attribute\HandlerAttributeInterface;
use MaxMessenger\Bot\MaxBot\CallbackHandler;
use MaxMessenger\Bot\MaxBot\CallbackJsonHandler;
use MaxMessenger\Bot\MaxBot\CommandHandler;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\BotAddedToChatEvent;
use MaxMessenger\Bot\MaxBot\Event\BotAdminPermissionsChangedEvent;
use MaxMessenger\Bot\MaxBot\Event\BotRemovedFromChatEvent;
use MaxMessenger\Bot\MaxBot\Event\BotStartedEvent;
use MaxMessenger\Bot\MaxBot\Event\BotStoppedEvent;
use MaxMessenger\Bot\MaxBot\Event\ChatTitleChangedEvent;
use MaxMessenger\Bot\MaxBot\Event\CommentCreatedEvent;
use MaxMessenger\Bot\MaxBot\Event\CommentEditedEvent;
use MaxMessenger\Bot\MaxBot\Event\CommentRemovedEvent;
use MaxMessenger\Bot\MaxBot\Event\DialogClearedEvent;
use MaxMessenger\Bot\MaxBot\Event\DialogMutedEvent;
use MaxMessenger\Bot\MaxBot\Event\DialogRemovedEvent;
use MaxMessenger\Bot\MaxBot\Event\DialogUnmutedEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageCallbackEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageEditedEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageRemovedEvent;
use MaxMessenger\Bot\MaxBot\Event\UnknownEvent;
use MaxMessenger\Bot\MaxBot\Event\UserAddedToChatEvent;
use MaxMessenger\Bot\MaxBot\Event\UserRemovedFromChatEvent;
use MaxMessenger\Bot\MaxBot\MaxBotHelperTrait;
use MaxMessenger\Bot\Model\Enum\UpdateType;
use MaxMessenger\Bot\Model\Response\Update;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionAttribute;
use ReflectionObject;
use SensitiveParameter;
use SensitiveParameterValue;
use Throwable;

use function array_unshift;
use function is_string;
use function mb_strlen;

/**
 * Обработчик обновлений бота с регистрацией обработчиков событий, команд и колбэков.
 */
final class MaxBot
{
    use MaxBotHelperTrait;

    /**
     * @var MaxApiClient Клиент API, через который бот выполняет запросы.
     */
    public readonly MaxApiClient $apiClient;

    /**
     * @param non-empty-string|MaxApiConfigInterface|MaxApiClient $accessTokenOrConfig Токен доступа,
     *     настройки подключения или готовый клиент API.
     * @param non-empty-string|null $secret Секрет для проверки входящих Webhook-запросов
     *     (minLength: 5, maxLength: 256) или `null`, чтобы отключить проверку.
     * @param bool $useRussianTrustedCaCertificates Доверять корневым сертификатам Минцифры, поставляемым с пакетом
     *     (см. {@see MaxApiConfig::useRussianTrustedCaCertificates()}). Применяется, только если передан токен.
     */
    public function __construct(
        #[SensitiveParameter]
        string|MaxApiConfigInterface|MaxApiClient $accessTokenOrConfig,
        #[SensitiveParameter]
        ?string $secret = null,
        bool $useRussianTrustedCaCertificates = false,
    ) {
        $this->apiClient = $accessTokenOrConfig instanceof MaxApiClient
            ? $accessTokenOrConfig
            : new MaxApiClient($accessTokenOrConfig, $useRussianTrustedCaCertificates);
        $this->setSecret($secret);
        $this->updateFilter = static fn(Update $update): bool => $update->isValid();
    }

    /**
     * @return MaxApiClient Клиент API, используемый ботом.
     */
    public function getApiClient(): MaxApiClient
    {
        return $this->apiClient;
    }

    /**
     * @return CallbackHandler Основной обработчик Callback-кнопок, создаваемый при первом вызове.
     */
    public function getCallbackHandler(): CallbackHandler
    {
        if ($this->callbackHandler === null) {
            $this->callbackHandler = new CallbackHandler();
            $this->onMessageCallback($this->callbackHandler->handle(...));
        }

        return $this->callbackHandler;
    }

    /**
     * @return CallbackJsonHandler Основной обработчик JSON-payload, создаваемый при первом вызове.
     */
    public function getCallbackJsonHandler(): CallbackJsonHandler
    {
        if ($this->callbackJsonHandler === null) {
            $this->callbackJsonHandler = new CallbackJsonHandler();
            $this->onMessageCallback($this->callbackJsonHandler->handle(...));
        }

        return $this->callbackJsonHandler;
    }

    /**
     * @return CommandHandler Основной обработчик команд, создаваемый при первом вызове.
     */
    public function getCommandHandler(): CommandHandler
    {
        if ($this->commandHandler === null) {
            $this->commandHandler = new CommandHandler();
            $this->onMessageCreated($this->commandHandler->handle(...), true);
        }

        return $this->commandHandler;
    }

    /**
     * @return (Closure(Update $update, Throwable $exception): void)|null Глобальный обработчик ошибок обновлений.
     */
    public function getUpdateErrorHandler(): ?Closure
    {
        return $this->updateErrorHandler;
    }

    /**
     * @return (Closure(Update $update): (bool|Update))|null Фильтр допуска или замены обновления либо `null`,
     *     если фильтрация отключена.
     */
    public function getUpdateFilter(): ?Closure
    {
        return $this->updateFilter;
    }

    /**
     * Запускает процесс обработки события из глобального контекста.
     *
     * @return bool `true`, если событие считается обработанным.
     */
    public function handleFromGlobal(): bool
    {
        return $this->handleUpdate(self::makeUpdateFromString($this->readRequestContentFromGlobal()));
    }

    /**
     * Запускает процесс обработки события из PSR-7 запроса.
     *
     * @param ServerRequestInterface $request Запрос от Webhook.
     * @return bool `true`, если событие считается обработанным.
     */
    public function handleFromRequest(ServerRequestInterface $request): bool
    {
        return $this->handleUpdate(self::makeUpdateFromString($this->readRequestContent($request)));
    }

    /**
     * Получает события с сервера через API и запускает процесс их обработки.
     *
     * @param int<1, 1000> $limit Максимальное количество событий для получения.
     * @param int<0, 90> $timeout Тайм-аут в секундах для долгого опроса.
     * @param int|null $marker Маркер для получения событий с конкретной позиции.
     *     Для получения всех ранее непрочитанных событий, передайте `null`.
     * @param array<UpdateType|string>|null $types Список типов событий, которые ваш бот хочет получать.
     * @return int|null Указатель на следующую страницу данных.
     */
    public function handleFromServer(
        int $limit = 1,
        int $timeout = 60,
        ?int $marker = null,
        ?array $types = null,
    ): ?int {
        $response = $this->apiClient->getUpdates($limit, $timeout, $marker, $types);

        foreach ($response->getUpdates() as $update) {
            $this->handleUpdate($update);
        }

        return $response->getMarker();
    }

    /**
     * Проверяет обновление фильтром и при допуске создаёт событие бота и запускает его обработку.
     *
     * @param Update $update Полученное обновление API.
     * @return bool `true`, если событие считается обработанным; `false` при отказе фильтра или перехваченной ошибке.
     * @throws Throwable Если глобальный обработчик ошибок не задан или сам выбросил исключение.
     */
    public function handleUpdate(Update $update): bool
    {
        try {
            if ($this->updateFilter !== null) {
                $filterResult = ($this->updateFilter)($update);
                if ($filterResult instanceof Update) {
                    $update = $filterResult;
                } elseif ($filterResult !== true) {
                    return false;
                }
            }

            return $this->handleEvent($this->makeEvent($update));
        } catch (Throwable $exception) {
            if ($this->updateErrorHandler === null) {
                throw $exception;
            }

            ($this->updateErrorHandler)($update, $exception);

            return false;
        }
    }

    /**
     * Регистрирует обработчик событий указанного типа обновления.
     *
     * @param UpdateType|string $updateType Тип обновления.
     * @param Closure(BaseEvent $event): (bool|void) $handler Обработчик события.
     * @return $this
     */
    public function on(UpdateType|string $updateType, Closure $handler): static
    {
        $this->typedHandlers[is_string($updateType) ? $updateType : $updateType->value][] = $handler;

        return $this;
    }

    /**
     * Регистрирует обработчик добавления бота в чат или канал.
     *
     * @param Closure(BotAddedToChatEvent $event): (bool|void) $handler Обработчик события.
     * @return $this
     */
    public function onBotAddedToChat(Closure $handler): static
    {
        $this->eventHandlers[BotAddedToChatEvent::class][] = $handler;

        return $this;
    }

    /**
     * Регистрирует обработчик изменения прав бота-администратора в чате или канале.
     *
     * @param Closure(BotAdminPermissionsChangedEvent $event): (bool|void) $handler Обработчик события.
     * @return $this
     */
    public function onBotAdminPermissionsChanged(Closure $handler): static
    {
        $this->eventHandlers[BotAdminPermissionsChangedEvent::class][] = $handler;

        return $this;
    }

    /**
     * Регистрирует обработчик удаления бота из чата или канала.
     *
     * @param Closure(BotRemovedFromChatEvent $event): (bool|void) $handler Обработчик события.
     * @return $this
     */
    public function onBotRemovedFromChat(Closure $handler): static
    {
        $this->eventHandlers[BotRemovedFromChatEvent::class][] = $handler;

        return $this;
    }

    /**
     * Регистрирует обработчик запуска бота пользователем.
     *
     * @param Closure(BotStartedEvent $event): (bool|void) $handler Обработчик события.
     * @return $this
     */
    public function onBotStarted(Closure $handler): static
    {
        $this->eventHandlers[BotStartedEvent::class][] = $handler;

        return $this;
    }

    /**
     * Регистрирует обработчик остановки бота пользователем.
     *
     * @param Closure(BotStoppedEvent $event): (bool|void) $handler Обработчик события.
     * @return $this
     */
    public function onBotStopped(Closure $handler): static
    {
        $this->eventHandlers[BotStoppedEvent::class][] = $handler;

        return $this;
    }

    /**
     * Регистрирует обработчик действия Callback-кнопки с текстовым payload.
     *
     * @param non-empty-string $name Имя действия.
     * @param Closure(MessageCallbackEvent $event, string $action, ?string $payload): (bool|void) $handler
     *     Обработчик действия с его именем и дополнительными данными.
     * @return $this
     */
    public function onCallback(string $name, Closure $handler): static
    {
        $this->getCallbackHandler()->onAction($name, $handler);

        return $this;
    }

    /**
     * Регистрирует обработчик действия Callback-кнопки с JSON-payload.
     *
     * @param non-empty-string $name Имя действия.
     * @param Closure(MessageCallbackEvent $event, string $action, array $payload): (bool|void) $handler
     *     Обработчик действия с его именем и декодированным JSON-payload.
     * @return $this
     */
    public function onCallbackJson(string $name, Closure $handler): static
    {
        $this->getCallbackJsonHandler()->onAction($name, $handler);

        return $this;
    }

    /**
     * Регистрирует обработчик изменения названия чата.
     *
     * @param Closure(ChatTitleChangedEvent $event): (bool|void) $handler Обработчик события.
     * @return $this
     */
    public function onChatTitleChanged(Closure $handler): static
    {
        $this->eventHandlers[ChatTitleChangedEvent::class][] = $handler;

        return $this;
    }

    /**
     * Регистрирует обработчик команды с указанным именем.
     *
     * @param non-empty-string $name Имя команды.
     * @param Closure(MessageCreatedEvent $event, string $command, ?string $payload): (bool|void) $handler
     *     Обработчик команды с её именем и дополнительными данными.
     * @return $this
     */
    public function onCommand(string $name, Closure $handler): static
    {
        $this->getCommandHandler()->onCommand($name, $handler);

        return $this;
    }

    /**
     * Регистрирует обработчик команд, которые не были обработаны ранее.
     *
     * @param Closure(MessageCreatedEvent $event, string $command, ?string $payload): (bool|void) $handler
     *     Обработчик оставшихся команд с именем команды и дополнительными данными.
     * @return $this
     */
    public function onCommands(Closure $handler): static
    {
        $this->getCommandHandler()->onCommands($handler);

        return $this;
    }

    /**
     * Регистрирует обработчик создания комментария к посту в канале.
     *
     * @param Closure(CommentCreatedEvent $event): (bool|void) $handler Обработчик события.
     * @return $this
     */
    public function onCommentCreated(Closure $handler): static
    {
        $this->eventHandlers[CommentCreatedEvent::class][] = $handler;

        return $this;
    }

    /**
     * Регистрирует обработчик редактирования комментария к посту в канале.
     *
     * @param Closure(CommentEditedEvent $event): (bool|void) $handler Обработчик события.
     * @return $this
     */
    public function onCommentEdited(Closure $handler): static
    {
        $this->eventHandlers[CommentEditedEvent::class][] = $handler;

        return $this;
    }

    /**
     * Регистрирует обработчик удаления комментария к посту в канале.
     *
     * @param Closure(CommentRemovedEvent $event): (bool|void) $handler Обработчик события.
     * @return $this
     */
    public function onCommentRemoved(Closure $handler): static
    {
        $this->eventHandlers[CommentRemovedEvent::class][] = $handler;

        return $this;
    }

    /**
     * Регистрирует обработчик очистки истории диалога с ботом.
     *
     * @param Closure(DialogClearedEvent $event): (bool|void) $handler Обработчик события.
     * @return $this
     */
    public function onDialogCleared(Closure $handler): static
    {
        $this->eventHandlers[DialogClearedEvent::class][] = $handler;

        return $this;
    }

    /**
     * Регистрирует обработчик отключения уведомлений в диалоге.
     *
     * @param Closure(DialogMutedEvent $event): (bool|void) $handler Обработчик события.
     * @return $this
     */
    public function onDialogMuted(Closure $handler): static
    {
        $this->eventHandlers[DialogMutedEvent::class][] = $handler;

        return $this;
    }

    /**
     * Регистрирует обработчик удаления диалога с ботом.
     *
     * @param Closure(DialogRemovedEvent $event): (bool|void) $handler Обработчик события.
     * @return $this
     */
    public function onDialogRemoved(Closure $handler): static
    {
        $this->eventHandlers[DialogRemovedEvent::class][] = $handler;

        return $this;
    }

    /**
     * Регистрирует обработчик включения уведомлений в диалоге.
     *
     * @param Closure(DialogUnmutedEvent $event): (bool|void) $handler Обработчик события.
     * @return $this
     */
    public function onDialogUnmuted(Closure $handler): static
    {
        $this->eventHandlers[DialogUnmutedEvent::class][] = $handler;

        return $this;
    }

    /**
     * Добавляет обработчик исключений выброшенных и не обработанных при обработке событий.
     *
     * @param Closure(Throwable $exception, BaseEvent $event): (bool|void) $handler Обработчик исключения. При
     *     возврате `true` событие отмечается как обработанное; остальные обработчики исключения всё равно вызываются.
     *     Завершение без возвращаемого значения само по себе не меняет статус события.
     * @return $this
     * @see EventException
     */
    public function onException(Closure $handler): static
    {
        $this->exceptionHandlers[] = $handler;

        return $this;
    }

    /**
     * Регистрирует обработчик событий, не обработанных предыдущими списками.
     *
     * @param Closure(BaseEvent $event): (bool|void) $handler Обработчик события.
     * @return $this
     */
    public function onFallback(Closure $handler): static
    {
        $this->fallbackHandlers[] = $handler;

        return $this;
    }

    /**
     * Регистрирует финальный обработчик, вызываемый после остальных списков.
     *
     * Вызывается и для событий, уже обработанных в предыдущих списках.
     * Проверить это можно по `$event->handledIn !== null`; свойство содержит список, в котором событие обработали.
     *
     * @param Closure(BaseEvent $event): (bool|void) $handler Обработчик события.
     *     При возврате `true` событие отмечается как обработанное, следующие финальные обработчики не вызываются.
     *     При завершении без возвращаемого значения статус по умолчанию остаётся `null`, и обход списка продолжается.
     * @return $this
     */
    public function onFinal(Closure $handler): static
    {
        $this->finalHandlers[] = $handler;

        return $this;
    }

    /**
     * Регистрирует обработчик нажатия callback-кнопки в чате или канале.
     *
     * @param Closure(MessageCallbackEvent $event): (bool|void) $handler Обработчик события.
     * @return $this
     */
    public function onMessageCallback(Closure $handler): static
    {
        $this->eventHandlers[MessageCallbackEvent::class][] = $handler;

        return $this;
    }

    /**
     * Регистрирует обработчик создания сообщения.
     *
     * @param Closure(MessageCreatedEvent $event): (bool|void) $handler Обработчик события.
     * @param bool $prepend Добавить обработчик в начало очереди.
     * @return $this
     */
    public function onMessageCreated(Closure $handler, bool $prepend = false): static
    {
        if ($prepend && isset($this->eventHandlers[MessageCreatedEvent::class])) {
            array_unshift($this->eventHandlers[MessageCreatedEvent::class], $handler);
        } else {
            $this->eventHandlers[MessageCreatedEvent::class][] = $handler;
        }

        return $this;
    }

    /**
     * Регистрирует обработчик редактирования сообщения.
     *
     * @param Closure(MessageEditedEvent $event): (bool|void) $handler Обработчик события.
     * @return $this
     */
    public function onMessageEdited(Closure $handler): static
    {
        $this->eventHandlers[MessageEditedEvent::class][] = $handler;

        return $this;
    }

    /**
     * Регистрирует обработчик удаления сообщения.
     *
     * @param Closure(MessageRemovedEvent $event): (bool|void) $handler Обработчик события.
     * @return $this
     */
    public function onMessageRemoved(Closure $handler): static
    {
        $this->eventHandlers[MessageRemovedEvent::class][] = $handler;

        return $this;
    }

    /**
     * Регистрирует предварительный обработчик, вызываемый до остальных списков.
     *
     * @param Closure(BaseEvent $event): (bool|void) $handler Обработчик события.
     *     При возврате `true` событие отмечается как обработанное.
     *     При завершении без возвращаемого значения статус по умолчанию остаётся `null`, и обход списка продолжается.
     * @return $this
     */
    public function onPrepare(Closure $handler): static
    {
        $this->prepareHandlers[] = $handler;

        return $this;
    }

    /**
     * Регистрирует обработчик обновления неизвестного типа.
     *
     * @param Closure(UnknownEvent $event): (bool|void) $handler Обработчик события.
     * @return $this
     */
    public function onUnknown(Closure $handler): static
    {
        $this->eventHandlers[UnknownEvent::class][] = $handler;

        return $this;
    }

    /**
     * Регистрирует обработчик добавления пользователя в чат или канал.
     *
     * @param Closure(UserAddedToChatEvent $event): (bool|void) $handler Обработчик события.
     * @return $this
     */
    public function onUserAddedToChat(Closure $handler): static
    {
        $this->eventHandlers[UserAddedToChatEvent::class][] = $handler;

        return $this;
    }

    /**
     * Регистрирует обработчик удаления пользователя из чата или канала.
     *
     * @param Closure(UserRemovedFromChatEvent $event): (bool|void) $handler Обработчик события.
     * @return $this
     */
    public function onUserRemovedFromChat(Closure $handler): static
    {
        $this->eventHandlers[UserRemovedFromChatEvent::class][] = $handler;

        return $this;
    }

    /**
     * Регистрирует публичные методы объекта, отмеченные атрибутами обработчиков, включая унаследованные.
     *
     * @param object $handlers Объект с методами, отмеченными атрибутами из пространства имён MaxBot\Attribute.
     * @return $this
     */
    public function registerHandlers(object $handlers): static
    {
        foreach ((new ReflectionObject($handlers))->getMethods() as $method) {
            if (!$method->isPublic()) {
                continue;
            }

            $attributes = $method->getAttributes(HandlerAttributeInterface::class, ReflectionAttribute::IS_INSTANCEOF);
            foreach ($attributes as $attribute) {
                $attribute->newInstance()->register($this, $method->getClosure($handlers));
            }
        }

        return $this;
    }

    /**
     * @param non-empty-string|null $secret Секрет для проверки входящих Webhook-запросов
     *     (minLength: 5, maxLength: 256) или `null`, чтобы отключить проверку.
     * @return $this
     */
    public function setSecret(?string $secret): self
    {
        if ($secret !== null) {
            $length = mb_strlen($secret, 'UTF-8');
            if ($length < 5) {
                throw new MinLengthException('secret', $length, 5);
            }
            if ($length > 256) {
                throw new MaxLengthException('secret', $length, 256);
            }
        }

        $this->secret = $secret !== null ? new SensitiveParameterValue($secret) : null;

        return $this;
    }

    /**
     * @param (Closure(Update $update, Throwable $exception): void)|null $updateErrorHandler Глобальный обработчик
     *     ошибок фильтра, создания события и его обработки; `null` отключает перехват исключений.
     * @return $this
     */
    public function setUpdateErrorHandler(?Closure $updateErrorHandler): self
    {
        $this->updateErrorHandler = $updateErrorHandler;

        return $this;
    }

    /**
     * @param (Closure(Update $update): (bool|Update))|null $updateFilter Фильтр, возвращающий `true` для допуска
     *     исходного обновления, `Update` для замены перед обработкой или `false` для отклонения обновления.
     *     Значение `null` отключает фильтрацию.
     * @return $this
     */
    public function setUpdateFilter(?Closure $updateFilter): self
    {
        $this->updateFilter = $updateFilter;

        return $this;
    }
}
