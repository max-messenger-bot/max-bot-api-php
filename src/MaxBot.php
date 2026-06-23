<?php

declare(strict_types=1);

namespace MaxMessenger\Bot;

use Closure;
use JsonException;
use MaxMessenger\Bot\Contract\MaxApiConfigInterface;
use MaxMessenger\Bot\Exception\MaxBot\Event\EventException;
use MaxMessenger\Bot\Exception\MaxBot\Update\BadRequestException;
use MaxMessenger\Bot\Exception\MaxBot\Update\InvalidSecretException;
use MaxMessenger\Bot\MaxBot\CallbackHandler;
use MaxMessenger\Bot\MaxBot\CallbackJsonHandler;
use MaxMessenger\Bot\MaxBot\CommandHandler;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\BotAddedToChatEvent;
use MaxMessenger\Bot\MaxBot\Event\BotRemovedFromChatEvent;
use MaxMessenger\Bot\MaxBot\Event\BotStartedEvent;
use MaxMessenger\Bot\MaxBot\Event\BotStoppedEvent;
use MaxMessenger\Bot\MaxBot\Event\ChatTitleChangedEvent;
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
use MaxMessenger\Bot\MaxBot\HandlerListType;
use MaxMessenger\Bot\Model\Enum\UpdateType;
use MaxMessenger\Bot\Model\Response\Update;
use SensitiveParameter;
use SensitiveParameterValue;
use Throwable;

use function array_key_exists;
use function array_unshift;
use function count;
use function file_get_contents;
use function hash_equals;
use function is_array;
use function is_string;
use function json_decode;
use function str_contains;
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
     * @var SensitiveParameterValue<non-empty-string>|null
     */
    private ?SensitiveParameterValue $secret;
    /**
     * @var array<string, list<Closure(BaseEvent $event): (bool|void)>>
     */
    private array $typedHandlers = [];

    /**
     * @param non-empty-string|MaxApiConfigInterface|MaxApiClient $accessTokenOrConfig
     * @param non-empty-string|null $secret
     */
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
     * Обрабатывает событие.
     *
     * @return bool `true`, если событие считается обработанным.
     */
    public function handleEvent(BaseEvent $event): bool
    {
        $event->handledIn = null;

        $list = $this->prepareHandlers;
        if ($list && self::handleEventUseHandlerList($event, $list, HandlerListType::Prepare)) {
            self::handleEventUseHandlerList($event, $this->finalHandlers, HandlerListType::Final);

            return true;
        }

        /** @var list<Closure(BaseEvent $event): void> $list */
        $list = $this->eventHandlers[$event::class] ?? [];
        if ($list && self::handleEventUseHandlerList($event, $list, HandlerListType::Event)) {
            self::handleEventUseHandlerList($event, $this->finalHandlers, HandlerListType::Final);

            return true;
        }

        $list = $this->typedHandlers[$event->update->getUpdateTypeRaw()] ?? [];
        if ($list && self::handleEventUseHandlerList($event, $list, HandlerListType::Typed)) {
            self::handleEventUseHandlerList($event, $this->finalHandlers, HandlerListType::Final);

            return true;
        }

        $list = $this->fallbackHandlers;
        if ($list && self::handleEventUseHandlerList($event, $list, HandlerListType::Fallback)) {
            self::handleEventUseHandlerList($event, $this->finalHandlers, HandlerListType::Final);

            return true;
        }

        return self::handleEventUseHandlerList($event, $this->finalHandlers, HandlerListType::Final);
    }

    /**
     * Обрабатывает событие используя переданный список обработчиков.
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
        ?bool $defaultIsHandled = null,
    ): bool {
        if (count($handlers) === 0) {
            return false;
        }

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
     * Запускает процесс обработки события из глобального контекста.
     *
     * @return bool `true`, если событие считается обработанным.
     */
    public function handleFromGlobal(): bool
    {
        return $this->handleUpdate(self::makeUpdateFromString($this->readRequestContentFromGlobal()));
    }

    /**
     * Получает события с сервера через API и запускает процесс их обработки.
     *
     * @param int<1, 1000> $limit Максимальное количество событий для получения (minimum: 1, maximum: 1000).
     * @param int<0, 90> $timeout Тайм-аут в секундах для долгого опроса (minimum: 0, maximum: 90).
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
     * Создаёт объект события бота на основе объекта события и запускает процесс обработки этого события.
     *
     * @return bool `true`, если событие считается обработанным.
     */
    public function handleUpdate(Update $update): bool
    {
        return $this->handleEvent($this->makeEvent($update));
    }

    /**
     * Создаёт объект события бота на основе объекта события.
     */
    public function makeEvent(Update $update): BaseEvent
    {
        return BaseEvent::new($update, $this->apiClient, $this->exceptionHandlers);
    }

    /**
     * Создаёт объект события из JSON-стоки.
     */
    public static function makeUpdateFromString(string $string): Update
    {
        try {
            $data = json_decode($string, true, 16, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new BadRequestException('The body does not contain valid JSON.', $e);
        }

        if (!is_array($data)) {
            throw new BadRequestException('Data is not array.');
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
     * Добавляет обработчик исключений выброшенных и не обработанных при обработке событий.
     *
     * @param Closure(Throwable $exception, BaseEvent $event): (bool|void) $handler
     * @return $this
     * @see EventException
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
     * Читает тело запроса из глобального контекста.
     *
     * @return string Тело запроса
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
        if ($secret && !hash_equals($secret, $_SERVER['HTTP_X_MAX_BOT_API_SECRET'] ?? '')) {
            throw new InvalidSecretException();
        }

        /** @psalm-suppress RiskyTruthyFalsyComparison */
        $body = @file_get_contents('php://input') ?: '';

        if (empty($body)) {
            throw new BadRequestException('Body is empty.');
        }

        if (strlen($body) !== (int) ($_SERVER['CONTENT_LENGTH'] ?? '')) {
            throw new BadRequestException('The Body size does not match the passed content-length.');
        }

        return $body;
    }

    /**
     * @param non-empty-string|null $secret
     * @return $this
     */
    public function setSecret(?string $secret): self
    {
        $this->secret = $secret !== null ? new SensitiveParameterValue($secret) : null;

        return $this;
    }
}
