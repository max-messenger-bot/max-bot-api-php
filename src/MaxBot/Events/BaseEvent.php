<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Events;

use ArrayObject;
use Closure;
use DateTimeImmutable;
use MaxMessenger\Bot\Exceptions\MaxBot\Events\EventException;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot\HandlerListType;
use MaxMessenger\Bot\Models\Responses\BotAddedToChatUpdate;
use MaxMessenger\Bot\Models\Responses\BotRemovedFromChatUpdate;
use MaxMessenger\Bot\Models\Responses\BotStartedUpdate;
use MaxMessenger\Bot\Models\Responses\BotStoppedUpdate;
use MaxMessenger\Bot\Models\Responses\ChatTitleChangedUpdate;
use MaxMessenger\Bot\Models\Responses\DialogClearedUpdate;
use MaxMessenger\Bot\Models\Responses\DialogMutedUpdate;
use MaxMessenger\Bot\Models\Responses\DialogRemovedUpdate;
use MaxMessenger\Bot\Models\Responses\DialogUnmutedUpdate;
use MaxMessenger\Bot\Models\Responses\MessageCallbackUpdate;
use MaxMessenger\Bot\Models\Responses\MessageCreatedUpdate;
use MaxMessenger\Bot\Models\Responses\MessageEditedUpdate;
use MaxMessenger\Bot\Models\Responses\MessageRemovedUpdate;
use MaxMessenger\Bot\Models\Responses\Update;
use MaxMessenger\Bot\Models\Responses\UserAddedToChatUpdate;
use MaxMessenger\Bot\Models\Responses\UserRemovedFromChatUpdate;
use Throwable;

/**
 * @psalm-consistent-constructor
 */
abstract class BaseEvent
{
    /**
     * @var HandlerListType|null Текущий список, в котором обрабатывается событие.
     */
    public ?HandlerListType $currentHandlerListType = null;
    /**
     * @var HandlerListType|null Указывает на список, в рамках которого было сообщение обработано
     */
    public ?HandlerListType $handledIn = null;
    /**
     * @var bool|null Статус обработки события в рамках текущего списка.
     */
    public bool|null $isHandled = null;

    /**
     * @param list<Closure(Throwable $exception, BaseEvent $event): (bool|void)> $exceptionHandlers
     */
    protected function __construct(
        public readonly Update $update,
        public readonly MaxApiClient $apiClient,
        private readonly array $exceptionHandlers,
        public ArrayObject $userData
    ) {
    }

    public function break(): never
    {
        EventException::break();
    }

    public function continue(): never
    {
        EventException::continue();
    }

    /**
     * @return DateTimeImmutable Unix-время, когда произошло событие.
     */
    public function getTimestamp(): DateTimeImmutable
    {
        return $this->update->getTimestamp();
    }

    /**
     * @return int Unix-время, когда произошло событие (Unix timestamp в миллисекундах).
     */
    public function getTimestampRaw(): int
    {
        return $this->update->getTimestampRaw();
    }

    /**
     * Обработать событие заданным обработчиком.
     *
     * @param Closure(static $event): (bool|void) $handler Обработчик события.
     * @return bool `true`, если событие обработано и дальнейшая обработка должна быть остановлена.
     */
    public function handle(Closure $handler, ?bool $defaultIsHandled = null): bool
    {
        try {
            $this->isHandled = $defaultIsHandled;

            $this->isHandled = $handler($this) ?? $this->isHandled;
        } catch (EventException $exception) {
            $this->isHandled = $exception->isHandled ?? $this->isHandled;
        } catch (Throwable $exception) {
            return $this->handleException($exception) ?? throw $exception;
        }

        return $this->isHandled === true;
    }

    public function markAsHandled(): void
    {
        $this->isHandled = true;
    }

    public function markAsUnhandled(): void
    {
        $this->isHandled = false;
    }

    /**
     * @param list<Closure(Throwable $exception, BaseEvent $event): void> $exceptionHandlers Exception handlers.
     *     If an exception occurs, event processing will be stopped and the exception will be thrown out.\
     *     You can handle and log the error:\
     *     - Calling {@see EventException::continue()} will continue processing the current event with other handlers.\
     *     - Calling {@see EventException::break()} will stop processing the current event,
     *       but continue processing other events.
     * @param ArrayObject $userData An array object for storing any user data.
     */
    public static function new(
        Update $update,
        MaxApiClient $maxApiClient,
        array $exceptionHandlers,
        ArrayObject $userData = new ArrayObject()
    ): self {
        $classMap = [
            BotAddedToChatUpdate::class => BotAddedToChatEvent::class,
            BotRemovedFromChatUpdate::class => BotRemovedFromChatEvent::class,
            BotStartedUpdate::class => BotStartedEvent::class,
            BotStoppedUpdate::class => BotStoppedEvent::class,
            ChatTitleChangedUpdate::class => ChatTitleChangedEvent::class,
            DialogClearedUpdate::class => DialogClearedEvent::class,
            DialogMutedUpdate::class => DialogMutedEvent::class,
            DialogRemovedUpdate::class => DialogRemovedEvent::class,
            DialogUnmutedUpdate::class => DialogUnmutedEvent::class,
            MessageCallbackUpdate::class => MessageCallbackEvent::class,
            MessageCreatedUpdate::class => MessageCreatedEvent::class,
            MessageEditedUpdate::class => MessageEditedEvent::class,
            MessageRemovedUpdate::class => MessageRemovedEvent::class,
            UserAddedToChatUpdate::class => UserAddedToChatEvent::class,
            UserRemovedFromChatUpdate::class => UserRemovedFromChatEvent::class,
        ];

        /** @psalm-var array<class-string<self>> $classMap Psalm bug */
        $className = $classMap[$update::class] ?? UnknownEvent::class;

        return new $className($update, $maxApiClient, $exceptionHandlers, $userData);
    }

    protected function handleException(Throwable $exception): ?bool
    {
        $this->isHandled = null;

        foreach ($this->exceptionHandlers as $exceptionHandler) {
            $saveIsHandled = $this->isHandled;

            try {
                $this->isHandled = $exceptionHandler($exception, $this) ?? $this->isHandled;
            } catch (EventException $exception) {
                $this->isHandled = $exception->isHandled ?? $this->isHandled;
            }

            if ($saveIsHandled !== null) {
                $this->isHandled = $saveIsHandled || $this->isHandled;
            }
        }

        return $this->isHandled;
    }
}
