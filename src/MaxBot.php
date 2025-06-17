<?php

declare(strict_types=1);

namespace MaxMessenger\Bot;

use Closure;
use MaxMessenger\Bot\Contracts\MaxApiConfigInterface;
use MaxMessenger\Bot\Exceptions\RuntimeException;
use MaxMessenger\Bot\MaxBot\CommandHandler;
use MaxMessenger\Bot\MaxBot\Events\BaseEvent;
use MaxMessenger\Bot\MaxBot\Events\BotAddedEvent;
use MaxMessenger\Bot\MaxBot\Events\BotRemovedEvent;
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
use MaxMessenger\Bot\MaxBot\Events\UserAddedEvent;
use MaxMessenger\Bot\MaxBot\Events\UserRemovedEvent;
use MaxMessenger\Bot\MaxBot\HandlerListType;
use MaxMessenger\Bot\Models\Enums\UpdateType;
use MaxMessenger\Bot\Models\Responses\Update;
use SensitiveParameter;
use SensitiveParameterValue;
use Throwable;

use function array_key_exists;
use function is_string;
use function strlen;

/**
 * @api
 */
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
     * @var list<Closure(BaseEvent): (bool|void)>
     */
    private array $fallbackHandlers = [];
    /**
     * @var list<Closure(BaseEvent): (bool|void)>
     */
    private array $finalHandlers = [];
    /**
     * @var list<Closure(BaseEvent): (bool|void)>
     */
    private array $prepareHandlers = [];
    /**
     * @var SensitiveParameterValue<string>|null
     */
    private ?SensitiveParameterValue $secret;
    /**
     * @var array<string, list<Closure(BaseEvent): (bool|void)>>
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
     * @api
     */
    public function getApiClient(): MaxApiClient
    {
        return $this->apiClient;
    }

    public function getCommandHandler(): CommandHandler
    {
        if ($this->commandHandler !== null) {
            return $this->commandHandler;
        }

        $this->commandHandler = new CommandHandler();

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
     * @api
     */
    public function handleEvent(BaseEvent $event): bool
    {
        $event->handledIn = null;

        if ($this->handleEventUseHandlerList($event, $this->prepareHandlers, HandlerListType::Prepare)) {
            $this->handleEventUseHandlerList($event, $this->finalHandlers, HandlerListType::Final);

            return true;
        }

        /** @var list<Closure(BaseEvent): void> $eventHandlers */
        $eventHandlers = $this->eventHandlers[$event::class] ?? [];
        if ($this->handleEventUseHandlerList($event, $eventHandlers, HandlerListType::Event)) {
            $this->handleEventUseHandlerList($event, $this->finalHandlers, HandlerListType::Final);

            return true;
        }

        $typedHandlers = $this->typedHandlers[$event->update->getUpdateTypeRaw()] ?? [];
        if ($this->handleEventUseHandlerList($event, $typedHandlers, HandlerListType::Typed)) {
            $this->handleEventUseHandlerList($event, $this->finalHandlers, HandlerListType::Final);

            return true;
        }

        if ($this->handleEventUseHandlerList($event, $this->fallbackHandlers, HandlerListType::Fallback)) {
            $this->handleEventUseHandlerList($event, $this->finalHandlers, HandlerListType::Final);

            return true;
        }

        $this->handleEventUseHandlerList($event, $this->finalHandlers, HandlerListType::Final);

        return false;
    }

    /**
     * Обработать событие используя переданный список обработчиков.
     *
     * Обработка прекращается, когда обработчик вернёт `true` (обработано).
     *
     * @param array<Closure(BaseEvent): (bool|void)> $handlers
     * @return bool `true`, если хоть один обработчик вернул `true`.
     * @api
     */
    public function handleEventUseHandlerList(
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
     *
     * @api
     */
    public function handleFromGlobal(): void
    {
        $this->handleUpdate($this->makeUpdateFromGlobal());
    }

    /**
     * Получить обновления с сервера через API и обработать их.
     *
     * @param int<1, 1000> $limit Maximum number of updates to be retrieved (minimum: 1, maximum: 1000).
     * @param int<0, 90> $timeout Timeout in seconds for long polling (minimum: 0, maximum: 90).
     * @param int|null $marker Pass `null` to get updates you didn't get yet.
     * @param array<UpdateType|string>|null $types List of update types your bot want to receive.
     * @return int|null Маркер, который можно использовать в последующих запросах,
     *     чтобы предотвратить случайный пропуск обновлений из-за ошибок.
     * @api
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
     * @api
     */
    public function handleUpdate(Update $update): bool
    {
        return $this->handleEvent($this->makeEvent($update));
    }

    /**
     * Создать объект события на основе объекта обновления.
     *
     * @api
     */
    public function makeEvent(Update $update): BaseEvent
    {
        return BaseEvent::new($update, $this->apiClient, $this->exceptionHandlers);
    }

    /**
     * Создать объект обновления из обрабатываемого запроса.
     *
     * @api
     */
    public function makeUpdateFromGlobal(): Update
    {
        $isPost = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
        $isJson = str_contains(($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json');
        $isContentLengthExists = ($_SERVER['CONTENT_LENGTH'] ?? null) !== null;

        if (!$isPost || !$isJson || !$isContentLengthExists) {
            throw new RuntimeException('Required: POST, application/json, content-length', 404);
        }

        $secret = $this->secret?->getValue();
        /** @psalm-suppress RiskyTruthyFalsyComparison */
        if ($secret && ($_SERVER['HTTP_X_MAX_BOT_API_SECRET'] ?? '') !== $secret) {
            throw new RuntimeException('Wrong Secret.', 403);
        }

        /** @psalm-suppress RiskyTruthyFalsyComparison */
        $body = file_get_contents('php://input') ?: '';

        if (empty($body)) {
            throw new RuntimeException('Body is empty.', 400);
        }

        if (strlen($body) !== (int)($_SERVER['CONTENT_LENGTH'] ?? '')) {
            throw new RuntimeException('The Body size does not match the passed content-length.', 400);
        }

        return Update::newFromJsonString($body);
    }

    /**
     * @param Closure(BaseEvent $event): (bool|void) $handler
     * @return $this
     * @api
     */
    public function on(UpdateType|string $updateType, Closure $handler): static
    {
        $this->typedHandlers[is_string($updateType) ? $updateType : $updateType->value][] = $handler;

        return $this;
    }

    /**
     * @param Closure(BotAddedEvent $event): (bool|void) $handler
     * @return $this
     * @api
     */
    public function onBotAdded(Closure $handler): static
    {
        $this->eventHandlers[BotAddedEvent::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(BotRemovedEvent $event): (bool|void) $handler
     * @return $this
     * @api
     */
    public function onBotRemoved(Closure $handler): static
    {
        $this->eventHandlers[BotRemovedEvent::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(BotStartedEvent $event): (bool|void) $handler
     * @return $this
     * @api
     */
    public function onBotStarted(Closure $handler): static
    {
        $this->eventHandlers[BotStartedEvent::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(BotStoppedEvent $event): (bool|void) $handler
     * @return $this
     * @api
     */
    public function onBotStopped(Closure $handler): static
    {
        $this->eventHandlers[BotStoppedEvent::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(ChatTitleChangedEvent $event): (bool|void) $handler
     * @return $this
     * @api
     */
    public function onChatTitleChanged(Closure $handler): static
    {
        $this->eventHandlers[ChatTitleChangedEvent::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(DialogClearedEvent $event): (bool|void) $handler
     * @return $this
     * @api
     */
    public function onDialogCleared(Closure $handler): static
    {
        $this->eventHandlers[DialogClearedEvent::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(DialogMutedEvent $event): (bool|void) $handler
     * @return $this
     * @api
     */
    public function onDialogMuted(Closure $handler): static
    {
        $this->eventHandlers[DialogMutedEvent::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(DialogRemovedEvent $event): (bool|void) $handler
     * @return $this
     * @api
     */
    public function onDialogRemoved(Closure $handler): static
    {
        $this->eventHandlers[DialogRemovedEvent::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(DialogUnmutedEvent $event): (bool|void) $handler
     * @return $this
     * @api
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
     * @api
     */
    public function onException(Closure $handler): static
    {
        $this->exceptionHandlers[] = $handler;

        return $this;
    }

    /**
     * @param Closure(BaseEvent $event): (bool|void) $handler
     * @return $this
     * @api
     */
    public function onFallback(Closure $handler): static
    {
        $this->fallbackHandlers[] = $handler;

        return $this;
    }

    /**
     * @param Closure(BaseEvent $event): (bool|void) $handler
     * @return $this
     * @api
     */
    public function onFinal(Closure $handler): static
    {
        $this->finalHandlers[] = $handler;

        return $this;
    }

    /**
     * @param Closure(MessageCallbackEvent $event): (bool|void) $handler
     * @return $this
     * @api
     */
    public function onMessageCallback(Closure $handler): static
    {
        $this->eventHandlers[MessageCallbackEvent::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(MessageCreatedEvent $event): (bool|void) $handler
     * @return $this
     * @api
     */
    public function onMessageCreated(Closure $handler): static
    {
        $this->eventHandlers[MessageCreatedEvent::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(MessageEditedEvent $event): (bool|void) $handler
     * @return $this
     * @api
     */
    public function onMessageEdited(Closure $handler): static
    {
        $this->eventHandlers[MessageEditedEvent::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(MessageRemovedEvent $event): (bool|void) $handler
     * @return $this
     * @api
     */
    public function onMessageRemoved(Closure $handler): static
    {
        $this->eventHandlers[MessageRemovedEvent::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(BaseEvent $event): (bool|void) $handler
     * @return $this
     * @api
     */
    public function onPrepare(Closure $handler): static
    {
        $this->prepareHandlers[] = $handler;

        return $this;
    }

    /**
     * @param Closure(UnknownEvent $event): (bool|void) $handler
     * @return $this
     * @api
     */
    public function onUnknown(Closure $handler): static
    {
        $this->eventHandlers[UnknownEvent::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(UserAddedEvent $event): (bool|void) $handler
     * @return $this
     * @api
     */
    public function onUserAdded(Closure $handler): static
    {
        $this->eventHandlers[UserAddedEvent::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(UserRemovedEvent $event): (bool|void) $handler
     * @return $this
     * @api
     */
    public function onUserRemoved(Closure $handler): static
    {
        $this->eventHandlers[UserRemovedEvent::class][] = $handler;

        return $this;
    }

    /**
     * @return $this
     * @api
     */
    public function setSecret(?string $secret): self
    {
        $this->secret = $secret !== null ? new SensitiveParameterValue($secret) : null;

        return $this;
    }
}
