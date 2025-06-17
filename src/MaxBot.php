<?php

declare(strict_types=1);

namespace MaxMessenger\Bot;

use Closure;
use MaxMessenger\Bot\Contracts\MaxApiConfigInterface;
use MaxMessenger\Bot\Events\BaseEvent;
use MaxMessenger\Bot\Events\BotAddedEvent;
use MaxMessenger\Bot\Events\BotRemovedEvent;
use MaxMessenger\Bot\Events\BotStartedEvent;
use MaxMessenger\Bot\Events\BotStoppedEvent;
use MaxMessenger\Bot\Events\ChatTitleChangedEvent;
use MaxMessenger\Bot\Events\DialogClearedEvent;
use MaxMessenger\Bot\Events\DialogMutedEvent;
use MaxMessenger\Bot\Events\DialogRemovedEvent;
use MaxMessenger\Bot\Events\DialogUnmutedEvent;
use MaxMessenger\Bot\Events\MessageCallbackEvent;
use MaxMessenger\Bot\Events\MessageCreatedEvent;
use MaxMessenger\Bot\Events\MessageEditedEvent;
use MaxMessenger\Bot\Events\MessageRemovedEvent;
use MaxMessenger\Bot\Events\UserAddedEvent;
use MaxMessenger\Bot\Events\UserRemovedEvent;
use MaxMessenger\Bot\Exceptions\RuntimeException;
use MaxMessenger\Bot\MaxBot\HandlerListType;
use MaxMessenger\Bot\Models\Enums\UpdateType;
use MaxMessenger\Bot\Models\Responses\BotAddedUpdate;
use MaxMessenger\Bot\Models\Responses\BotRemovedUpdate;
use MaxMessenger\Bot\Models\Responses\BotStartedUpdate;
use MaxMessenger\Bot\Models\Responses\BotStoppedUpdate;
use MaxMessenger\Bot\Models\Responses\ChatTitleChangedUpdate;
use MaxMessenger\Bot\Models\Responses\DialogMutedUpdate;
use MaxMessenger\Bot\Models\Responses\DialogRemovedUpdate;
use MaxMessenger\Bot\Models\Responses\DialogUnmutedUpdate;
use MaxMessenger\Bot\Models\Responses\MessageCallbackUpdate;
use MaxMessenger\Bot\Models\Responses\MessageCreatedUpdate;
use MaxMessenger\Bot\Models\Responses\MessageEditedUpdate;
use MaxMessenger\Bot\Models\Responses\MessageRemovedUpdate;
use MaxMessenger\Bot\Models\Responses\Update;
use MaxMessenger\Bot\Models\Responses\UserAddedUpdate;
use MaxMessenger\Bot\Models\Responses\UserRemovedUpdate;
use SensitiveParameter;
use SensitiveParameterValue;
use Throwable;

use function in_array;
use function is_string;
use function strlen;

/**
 * @api
 */
final class MaxBot
{
    public readonly MaxApiClient $apiClient;
    /**
     * @var array<class-string<Update>, list<Closure>>
     */
    private array $eventHandlers = [];
    /**
     * @var list<Closure(Throwable $exception): void>
     */
    private array $exceptionHandlers = [];
    /**
     * @var list<Closure(BaseEvent): void>
     */
    private array $fallbackHandlers = [];
    /**
     * @var list<Closure(BaseEvent): void>
     */
    private array $finalHandlers = [];
    /**
     * @var list<Closure(BaseEvent): void>
     */
    private array $prepareHandlers = [];
    /**
     * @var SensitiveParameterValue<string>|null
     */
    private ?SensitiveParameterValue $secret;
    /**
     * @var array<string, list<Closure(BaseEvent): void>>
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

    /**
     * @api
     */
    public function handleEvent(BaseEvent $event): bool
    {
        $event->handledIn = null;

        if ($this->handleEventUseHandlerList($event, $this->prepareHandlers, HandlerListType::Prepare)) {
            return true;
        }

        /** @var list<Closure(BaseEvent): void> $eventHandlers */
        $eventHandlers = $this->eventHandlers[$event->update::class] ?? [];
        if ($this->handleEventUseHandlerList($event, $eventHandlers, HandlerListType::Event)) {
            return true;
        }

        $typedHandlers = $this->typedHandlers[$event->update->getUpdateTypeRaw()] ?? [];
        if ($this->handleEventUseHandlerList($event, $typedHandlers, HandlerListType::Typed)) {
            return true;
        }

        if ($this->handleEventUseHandlerList($event, $this->fallbackHandlers, HandlerListType::Fallback)) {
            return true;
        }

        return false;
    }

    /**
     * @param array<Closure(BaseEvent): void> $handlers
     * @api
     */
    public function handleEventUseHandlerList(BaseEvent $event, array $handlers, HandlerListType $handlerListType): bool
    {
        $event->currentHandlerList = $handlerListType;

        $defaultIsHandled = in_array($handlerListType, [HandlerListType::Prepare, HandlerListType::Final], true)
            ? false
            : null;
        foreach ($handlers as $handler) {
            if ($event->handle($handler, $defaultIsHandled)) {
                if ($handlerListType !== HandlerListType::Final) {
                    $event->handledIn = $handlerListType;
                    $this->handleEventUseHandlerList($event, $this->finalHandlers, HandlerListType::Final);
                }

                return true;
            }
        }

        return false;
    }

    /**
     * @api
     */
    public function handleFromGlobal(): void
    {
        $this->handleUpdate($this->makeUpdateFromGlobal());
    }

    /**
     * @param int<1, 1000> $limit Maximum number of updates to be retrieved (minimum: 1, maximum: 1000).
     * @param int<0, 90> $timeout Timeout in seconds for long polling (minimum: 0, maximum: 90).
     * @param int|null $marker Pass `null` to get updates you didn't get yet.
     * @param array<UpdateType|string>|null $types List of update types your bot want to receive.
     * @return int|null Marker.
     * @api
     */
    public function handleFromServer(
        int $limit = 10,
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
     * @api
     */
    public function handleUpdate(Update $update): bool
    {
        return $this->handleEvent($this->makeEvent($update));
    }

    /**
     * @api
     */
    public function makeEvent(Update $update): BaseEvent
    {
        return BaseEvent::new($update, $this->apiClient, $this->exceptionHandlers);
    }

    /**
     * @api
     */
    public function makeUpdateFromGlobal(): Update
    {
        $isPost = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
        $isJson = str_contains(($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json');
        $isContentLengthExists = !empty($_SERVER['CONTENT_LENGTH']);

        if (!$isPost || !$isJson || !$isContentLengthExists) {
            throw new RuntimeException('Required: POST, application/json, content-length', 404);
        }

        $secret = $this->secret?->getValue();
        /** @psalm-suppress RiskyTruthyFalsyComparison */
        if (!$secret && ($_SERVER['HTTP_X_MAX_BOT_API_SECRET'] ?? '') !== $secret) {
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
     * @param Closure(BaseEvent $event): void $handler
     * @return $this
     * @api
     */
    public function on(UpdateType|string $updateType, Closure $handler): static
    {
        $this->typedHandlers[is_string($updateType) ? $updateType : $updateType->value][] = $handler;

        return $this;
    }

    /**
     * @param Closure(BotAddedEvent $event): void $handler
     * @return $this
     * @api
     */
    public function onBotAdded(Closure $handler): static
    {
        $this->eventHandlers[BotAddedUpdate::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(BotRemovedEvent $event): void $handler
     * @return $this
     * @api
     */
    public function onBotRemoved(Closure $handler): static
    {
        $this->eventHandlers[BotRemovedUpdate::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(BotStartedEvent $event): void $handler
     * @return $this
     * @api
     */
    public function onBotStarted(Closure $handler): static
    {
        $this->eventHandlers[BotStartedUpdate::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(BotStoppedEvent $event): void $handler
     * @return $this
     * @api
     */
    public function onBotStopped(Closure $handler): static
    {
        $this->eventHandlers[BotStoppedUpdate::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(ChatTitleChangedEvent $event): void $handler
     * @return $this
     * @api
     */
    public function onChatTitleChanged(Closure $handler): static
    {
        $this->eventHandlers[ChatTitleChangedUpdate::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(DialogClearedEvent $event): void $handler
     * @return $this
     * @api
     */
    public function onDialogCleared(Closure $handler): static
    {
        $this->eventHandlers[DialogMutedUpdate::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(DialogMutedEvent $event): void $handler
     * @return $this
     * @api
     */
    public function onDialogMuted(Closure $handler): static
    {
        $this->eventHandlers[DialogMutedUpdate::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(DialogRemovedEvent $event): void $handler
     * @return $this
     * @api
     */
    public function onDialogRemoved(Closure $handler): static
    {
        $this->eventHandlers[DialogRemovedUpdate::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(DialogUnmutedEvent $event): void $handler
     * @return $this
     * @api
     */
    public function onDialogUnmuted(Closure $handler): static
    {
        $this->eventHandlers[DialogUnmutedUpdate::class][] = $handler;

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
     * @param Closure(Throwable $exception): void $handler
     * @return $this
     * @api
     */
    public function onException(Closure $handler): static
    {
        $this->exceptionHandlers[] = $handler;

        return $this;
    }

    /**
     * @param Closure(BaseEvent $event): void $handler
     * @return $this
     * @api
     */
    public function onFallback(Closure $handler): static
    {
        $this->fallbackHandlers[] = $handler;

        return $this;
    }

    /**
     * @param Closure(BaseEvent $event): void $handler
     * @return $this
     * @api
     */
    public function onFinal(Closure $handler): static
    {
        $this->finalHandlers[] = $handler;

        return $this;
    }

    /**
     * @param Closure(MessageCallbackEvent $event): void $handler
     * @return $this
     * @api
     */
    public function onMessageCallback(Closure $handler): static
    {
        $this->eventHandlers[MessageCallbackUpdate::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(MessageCreatedEvent $event): void $handler
     * @return $this
     * @api
     */
    public function onMessageCreated(Closure $handler): static
    {
        $this->eventHandlers[MessageCreatedUpdate::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(MessageEditedEvent $event): void $handler
     * @return $this
     * @api
     */
    public function onMessageEdited(Closure $handler): static
    {
        $this->eventHandlers[MessageEditedUpdate::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(MessageRemovedEvent $event): void $handler
     * @return $this
     * @api
     */
    public function onMessageRemoved(Closure $handler): static
    {
        $this->eventHandlers[MessageRemovedUpdate::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(BaseEvent $event): void $handler
     * @return $this
     * @api
     */
    public function onPrepare(Closure $handler): static
    {
        $this->prepareHandlers[] = $handler;

        return $this;
    }

    /**
     * @param Closure(UserAddedEvent $event): void $handler
     * @return $this
     * @api
     */
    public function onUserAdded(Closure $handler): static
    {
        $this->eventHandlers[UserAddedUpdate::class][] = $handler;

        return $this;
    }

    /**
     * @param Closure(UserRemovedEvent $event): void $handler
     * @return $this
     * @api
     */
    public function onUserRemoved(Closure $handler): static
    {
        $this->eventHandlers[UserRemovedUpdate::class][] = $handler;

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
