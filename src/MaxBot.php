<?php

declare(strict_types=1);

namespace MaxMessenger\Bot;

use Closure;
use JsonException;
use MaxMessenger\Bot\Contracts\MaxApiConfigInterface;
use MaxMessenger\Bot\Exceptions\MaxBot\Events\EventException;
use MaxMessenger\Bot\Exceptions\MaxBot\Update\BadRequestException;
use MaxMessenger\Bot\Exceptions\MaxBot\Update\InvalidSecretException;
use MaxMessenger\Bot\Exceptions\RuntimeException;
use MaxMessenger\Bot\MaxBot\CallbackHandler;
use MaxMessenger\Bot\MaxBot\CallbackJsonHandler;
use MaxMessenger\Bot\MaxBot\CommandHandler;
use MaxMessenger\Bot\MaxBot\Events\BaseEvent;
use MaxMessenger\Bot\MaxBot\Events\BotAddedToChatEvent;
use MaxMessenger\Bot\MaxBot\Events\BotRemovedFromChatEvent;
use MaxMessenger\Bot\MaxBot\Events\BotStartedEvent;
use MaxMessenger\Bot\MaxBot\Events\BotStoppedEvent;
use MaxMessenger\Bot\MaxBot\Events\ChatTitleChangedEvent;
use MaxMessenger\Bot\MaxBot\Events\DialogClearedEvent;
use MaxMessenger\Bot\MaxBot\Events\DialogMutedEvent;
use MaxMessenger\Bot\MaxBot\Events\DialogRemovedEvent;
use MaxMessenger\Bot\MaxBot\Events\DialogUnmutedEvent;
use MaxMessenger\Bot\MaxBot\Events\MessageCallbackEvent;
use MaxMessenger\Bot\MaxBot\Events\MessageCreatedEvent;
use MaxMessenger\Bot\MaxBot\Events\MessageEditedEvent;
use MaxMessenger\Bot\MaxBot\Events\MessageRemovedEvent;
use MaxMessenger\Bot\MaxBot\Events\UnknownEvent;
use MaxMessenger\Bot\MaxBot\Events\UserAddedToChatEvent;
use MaxMessenger\Bot\MaxBot\Events\UserRemovedFromChatEvent;
use MaxMessenger\Bot\MaxBot\HandlerListType;
use MaxMessenger\Bot\Models\Enums\UpdateType;
use MaxMessenger\Bot\Models\Responses\Update;
use SensitiveParameter;
use SensitiveParameterValue;
use Throwable;

use function array_key_exists;
use function is_array;
use function is_string;
use function strlen;

final class MaxBot
{
    public readonly MaxApiClient $apiClient;
    private ?CommandHandler $commandHandler = null;
    /**
     * @var array<class-string<BaseEvent>, list<Closure>>
     */
    private array $eventHandlers = [];
    /**
     * @var list<Closure(Throwable $exception, BaseEvent $event): (bool|void)>
     */
    private array $exceptionHandlers = [];
    /**
     * @var list<Closure(BaseEvent $event): (bool|void)>
     */
    private array $fallbackHandlers = [];
    /**
     * @var list<Closure(BaseEvent $event): (bool|void)>
     */
    private array $finalHandlers = [];
    /**
     * @var list<Closure(BaseEvent $event): (bool|void)>
     */
    private array $prepareHandlers = [];
    /**
     * @var SensitiveParameterValue<string>|null
     */
    private ?SensitiveParameterValue $secret;
    /**
     * @var array<string, list<Closure(BaseEvent $event): (bool|void)>>
     */
    private array $typedHandlers = [];

    public function __construct(
        #[SensitiveParameter]
        string|MaxApiConfigInterface|MaxApiClient $accessTokenOrConfig,
        #[SensitiveParameter]
        ?string $secret = null,
    ) {
        $this->apiClient = $accessTokenOrConfig instanceof MaxApiClient
            ? $accessTokenOrConfig
            : new MaxApiClient($accessTokenOrConfig);
        $this->setSecret($secret);
    }

    /**
     * @param non-empty-string|null $actionSeparator
     * @param positive-int $actionMaxLength
     */
    public function addCallbackHandler(?string $actionSeparator = null, int $actionMaxLength = 64): CallbackHandler
    {
        $callbackHandler = new CallbackHandler([], $actionSeparator, $actionMaxLength);

        $this->eventHandlers[MessageCallbackEvent::class][] = $callbackHandler->handle(...);

        return $callbackHandler;
    }

    /**
     * @param non-empty-string $actionKey
     */
    public function addCallbackJsonHandler(string $actionKey): CallbackJsonHandler
    {
        $callbackHandler = new CallbackJsonHandler($actionKey);

        $this->eventHandlers[MessageCallbackEvent::class][] = $callbackHandler->handle(...);

        return $callbackHandler;
    }

    public function getApiClient(): MaxApiClient
    {
        return $this->apiClient;
    }

    /**
     * @param non-empty-string|null $commandSeparator
     * @param positive-int $commandMaxLength
     */
    public function getCommandHandler(?string $commandSeparator = null, int $commandMaxLength = 64): CommandHandler
    {
        if ($this->commandHandler !== null) {
            return $this->commandHandler;
        }

        $this->commandHandler = new CommandHandler([], [], $commandSeparator, $commandMaxLength);

        if (array_key_exists(MessageCreatedEvent::class, $this->eventHandlers)) {
            array_unshift($this->eventHandlers[MessageCreatedEvent::class], $this->commandHandler->handle(...));
        } else {
            $this->eventHandlers[MessageCreatedEvent::class] = [$this->commandHandler->handle(...)];
        }

        return $this->commandHandler;
    }

    /**
     * Обработать событие.
     *
     * @return bool `true`, если событие считается обработанным.
     */
    public function handleEvent(BaseEvent $event): bool
    {
        $event->handledIn = null;

        if (self::handleEventUseHandlerList($event, $this->prepareHandlers, HandlerListType::Prepare)) {
            self::handleEventUseHandlerList($event, $this->finalHandlers, HandlerListType::Final);

            return true;
        }

        /** @var list<Closure(BaseEvent $event): void> $eventHandlers */
        $eventHandlers = $this->eventHandlers[$event::class] ?? [];
        if (self::handleEventUseHandlerList($event, $eventHandlers, HandlerListType::Event)) {
            self::handleEventUseHandlerList($event, $this->finalHandlers, HandlerListType::Final);

            return true;
        }

        $typedHandlers = $this->typedHandlers[$event->update->getUpdateTypeRaw()] ?? [];
        if (self::handleEventUseHandlerList($event, $typedHandlers, HandlerListType::Typed)) {
            self::handleEventUseHandlerList($event, $this->finalHandlers, HandlerListType::Final);

            return true;
        }

        if (self::handleEventUseHandlerList($event, $this->fallbackHandlers, HandlerListType::Fallback)) {
            self::handleEventUseHandlerList($event, $this->finalHandlers, HandlerListType::Final);

            return true;
        }

        self::handleEventUseHandlerList($event, $this->finalHandlers, HandlerListType::Final);

        return false;
    }

    /**
     * Обработать событие используя переданный список обработчиков.
     *
     * Обработка прекращается, когда обработчик вернёт `true` (обработано).
     *
     * @param array<Closure(BaseEvent $event): (bool|void)> $handlers
     * @return bool `true`, если хоть один обработчик вернул `true`.
     */
    public static function handleEventUseHandlerList(
        BaseEvent $event,
        array $handlers,
        HandlerListType $handlerListType = HandlerListType::Custom,
        bool $defaultIsHandled = null
    ): bool {
        $saveListType = $event->currentHandlerListType;

        foreach ($handlers as $handler) {
            $event->currentHandlerListType = $handlerListType;

            if ($event->handle($handler, $defaultIsHandled)) {
                if ($handlerListType !== HandlerListType::Final) {
                    $event->handledIn = $handlerListType;
                }

                $event->currentHandlerListType = $saveListType;

                return true;
            }
        }

        $event->currentHandlerListType = $saveListType;

        return false;
    }

    /**
     * Запускает процесс обработки обновления из обрабатываемого запроса.
     */
    public function handleFromGlobal(): void
    {
        $this->handleUpdate(self::makeUpdateFromString($this->readRequestContentFromGlobal()));
    }

    /**
     * Получить обновления с сервера через API и обработать их.
     *
     * @param int<1, 1000> $limit Максимальное количество обновлений для получения (minimum: 1, maximum: 1000).
     * @param int<0, 90> $timeout Тайм-аут в секундах для долгого опроса (minimum: 0, maximum: 90).
     * @param int|null $marker Маркер для получения обновлений с конкретной позиции.
     *     Для получения всех ранее непрочитанных обновлений, передайте `null`.
     * @param array<UpdateType|string>|null $types Список типов обновлений, которые ваш бот хочет получать.
     * @return int|null Указатель на следующую страницу данных.
     */
    public function handleFromServer(
        int $limit = 1,
        int $timeout = 60,
        ?int $marker = null,
        ?array $types = null
    ): ?int {
        $response = $this->apiClient->getUpdates($limit, $timeout, $marker, $types);

        foreach ($response->getUpdates() as $update) {
            $this->handleUpdate($update);
        }

        return $response->getMarker();
    }

    /**
     * Создать объект события на основе объекта обновления и обработать это событие.
     *
     * @return bool `true`, если событие считается обработанным.
     */
    public function handleUpdate(Update $update): bool
    {
        return $this->handleEvent($this->makeEvent($update));
    }

    /**
     * Создать объект события на основе объекта обновления.
     */
    public function makeEvent(Update $update): BaseEvent
    {
        return BaseEvent::new($update, $this->apiClient, $this->exceptionHandlers);
    }

    /**
     * Создать объект обновления из JSON-стоки.
     */
    public static function makeUpdateFromString(string $string): Update
    {
        try {
            $data = json_decode($string, true, 16, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new BadRequestException('The body does not contain valid JSON.', $e);
        }

        if (!is_array($data)) {
            throw new RuntimeException('Data is not array.');
        }

        return Update::newFromData($data);
    }

    /**
     * @param Closure(BaseEvent $event): (bool|void) $handler
     * @return $this
     */
    public function on(UpdateType|string $updateType, Closure $handler): static
    {
        $this->typedHandlers[is_string($updateType) ? $updateType : $updateType->value][] = $handler;

        return $this;
    }

    /**
     * @param Closure(BotAddedToChatEvent $event): (bool|void) $handler
     * @return $this
     */
    public function onBotAddedToChat(Closure $handler): static
    {
        $this->eventHandlers[BotAddedToChatEvent::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(BotRemovedFromChatEvent $event): (bool|void) $handler
     * @return $this
     */
    public function onBotRemovedFromChat(Closure $handler): static
    {
        $this->eventHandlers[BotRemovedFromChatEvent::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(BotStartedEvent $event): (bool|void) $handler
     * @return $this
     */
    public function onBotStarted(Closure $handler): static
    {
        $this->eventHandlers[BotStartedEvent::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(BotStoppedEvent $event): (bool|void) $handler
     * @return $this
     */
    public function onBotStopped(Closure $handler): static
    {
        $this->eventHandlers[BotStoppedEvent::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(ChatTitleChangedEvent $event): (bool|void) $handler
     * @return $this
     */
    public function onChatTitleChanged(Closure $handler): static
    {
        $this->eventHandlers[ChatTitleChangedEvent::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(DialogClearedEvent $event): (bool|void) $handler
     * @return $this
     */
    public function onDialogCleared(Closure $handler): static
    {
        $this->eventHandlers[DialogClearedEvent::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(DialogMutedEvent $event): (bool|void) $handler
     * @return $this
     */
    public function onDialogMuted(Closure $handler): static
    {
        $this->eventHandlers[DialogMutedEvent::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(DialogRemovedEvent $event): (bool|void) $handler
     * @return $this
     */
    public function onDialogRemoved(Closure $handler): static
    {
        $this->eventHandlers[DialogRemovedEvent::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(DialogUnmutedEvent $event): (bool|void) $handler
     * @return $this
     */
    public function onDialogUnmuted(Closure $handler): static
    {
        $this->eventHandlers[DialogUnmutedEvent::class][] = $handler;

        return $this;
    }

    /**
     * Add exception handler.
     *
     * If an exception occurs, event processing will be stopped and the exception will be thrown out.
     * You can handle and log the error:
     * - Calling {@see EventException::continue()} will continue processing the current event with other handlers.
     * - Calling {@see EventException::break()} will stop processing the current event,
     *   but continue processing other events.
     *
     * @param Closure(Throwable $exception, BaseEvent $event): (bool|void) $handler
     * @return $this
     */
    public function onException(Closure $handler): static
    {
        $this->exceptionHandlers[] = $handler;

        return $this;
    }

    /**
     * @param Closure(BaseEvent $event): (bool|void) $handler
     * @return $this
     */
    public function onFallback(Closure $handler): static
    {
        $this->fallbackHandlers[] = $handler;

        return $this;
    }

    /**
     * @param Closure(BaseEvent $event): (bool|void) $handler
     * @return $this
     */
    public function onFinal(Closure $handler): static
    {
        $this->finalHandlers[] = $handler;

        return $this;
    }

    /**
     * @param Closure(MessageCallbackEvent $event): (bool|void) $handler
     * @return $this
     */
    public function onMessageCallback(Closure $handler): static
    {
        $this->eventHandlers[MessageCallbackEvent::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(MessageCreatedEvent $event): (bool|void) $handler
     * @return $this
     */
    public function onMessageCreated(Closure $handler): static
    {
        $this->eventHandlers[MessageCreatedEvent::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(MessageEditedEvent $event): (bool|void) $handler
     * @return $this
     */
    public function onMessageEdited(Closure $handler): static
    {
        $this->eventHandlers[MessageEditedEvent::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(MessageRemovedEvent $event): (bool|void) $handler
     * @return $this
     */
    public function onMessageRemoved(Closure $handler): static
    {
        $this->eventHandlers[MessageRemovedEvent::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(BaseEvent $event): (bool|void) $handler
     * @return $this
     */
    public function onPrepare(Closure $handler): static
    {
        $this->prepareHandlers[] = $handler;

        return $this;
    }

    /**
     * @param Closure(UnknownEvent $event): (bool|void) $handler
     * @return $this
     */
    public function onUnknown(Closure $handler): static
    {
        $this->eventHandlers[UnknownEvent::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(UserAddedToChatEvent $event): (bool|void) $handler
     * @return $this
     */
    public function onUserAddedToChat(Closure $handler): static
    {
        $this->eventHandlers[UserAddedToChatEvent::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(UserRemovedFromChatEvent $event): (bool|void) $handler
     * @return $this
     */
    public function onUserRemovedFromChat(Closure $handler): static
    {
        $this->eventHandlers[UserRemovedFromChatEvent::class][] = $handler;

        return $this;
    }

    /**
     * Прочитать содержимое запроса из глобального контекста.
     */
    public function readRequestContentFromGlobal(): string
    {
        $isPost = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
        $isJson = str_contains(($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json');
        $isContentLengthExists = ($_SERVER['CONTENT_LENGTH'] ?? null) !== null;

        if (!$isPost || !$isJson || !$isContentLengthExists) {
            throw new BadRequestException('Required: POST, application/json, content-length');
        }

        $secret = $this->secret?->getValue();
        /** @psalm-suppress RiskyTruthyFalsyComparison */
        if ($secret && ($_SERVER['HTTP_X_MAX_BOT_API_SECRET'] ?? '') !== $secret) {
            throw new InvalidSecretException();
        }

        /** @psalm-suppress RiskyTruthyFalsyComparison */
        $body = file_get_contents('php://input') ?: '';

        if (empty($body)) {
            throw new BadRequestException('Body is empty.');
        }

        if (strlen($body) !== (int)($_SERVER['CONTENT_LENGTH'] ?? '')) {
            throw new BadRequestException('The Body size does not match the passed content-length.');
        }

        return $body;
    }

    /**
     * @return $this
     */
    public function setSecret(?string $secret): self
    {
        $this->secret = $secret !== null ? new SensitiveParameterValue($secret) : null;

        return $this;
    }
}
