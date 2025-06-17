<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Events;

use ArrayObject;
use Closure;
use MaxMessenger\Bot\Exceptions\EventException;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot\HandlerListType;
use MaxMessenger\Bot\Models\Responses\BotAddedUpdate;
use MaxMessenger\Bot\Models\Responses\BotRemovedUpdate;
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
use MaxMessenger\Bot\Models\Responses\UserAddedUpdate;
use MaxMessenger\Bot\Models\Responses\UserRemovedUpdate;
use Throwable;

/**
 * @psalm-consistent-constructor
 * @api
 */
abstract class BaseEvent
{
    /**
     * @var HandlerListType|null Текущий список, в котором обрабатывается событие.
     * @api
     */
    public ?HandlerListType $currentHandlerListType = null;
    /**
     * @var HandlerListType|null Указывает на список, в рамках которого было сообщение обработано
     * @api
     */
    public ?HandlerListType $handledIn = null;
    /**
     * @var bool|null Статус обработки события в рамках текущего списка.
     */
    public bool|null $isHandled = null;

    /**
     * @param list<Closure(Throwable $exception, BaseEvent $event): (bool|void)> $exceptionHandlers
     */
    private function __construct(
        public readonly Update $update,
        public readonly MaxApiClient $apiClient,
        private readonly array $exceptionHandlers,
        public ArrayObject $userData
    ) {
    }

    /**
     * @api
     */
    public function break(): never
    {
        EventException::break();
    }

    /**
     * @api
     */
    public function continue(): never
    {
        EventException::continue();
    }

    /**
     * Обработать событие заданным обработчиком.
     *
     * @param Closure(static): (bool|void) $handler Обработчик события.
     * @return bool `true`, если событие обработано и дальнейшая обработка должна быть остановлена.
     * @noinspection PhpDocMissingThrowsInspection
     * @api
     */
    public function handle(Closure $handler, ?bool $defaultIsHandled = null): bool
    {
        try {
            $this->isHandled = $defaultIsHandled;

            $this->isHandled = $handler($this) ?? $this->isHandled;
        } catch (EventException $exception) {
            $this->isHandled = $exception->isHandled ?? $this->isHandled;
        } catch (Throwable $exception) {
            /** @noinspection PhpUnhandledExceptionInspection */
            return $this->handleException($exception);
        }

        return $this->isHandled === true;
    }

    /**
     * @api
     */
    public function markAsHandled(): void
    {
        $this->isHandled = true;
    }

    /**
     * @api
     */
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
     * @api
     */
    public static function new(
        Update $update,
        MaxApiClient $maxApiClient,
        array $exceptionHandlers,
        ArrayObject $userData = new ArrayObject()
    ): self {
        $classMap = [
            BotAddedUpdate::class => BotAddedEvent::class,
            BotRemovedUpdate::class => BotRemovedEvent::class,
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
            UserAddedUpdate::class => UserAddedEvent::class,
            UserRemovedUpdate::class => UserRemovedEvent::class,
        ];

        /** @psalm-var array<class-string<self>> $classMap Psalm bug */
        $className = $classMap[$update::class] ?? UnknownEvent::class;

        return new $className($update, $maxApiClient, $exceptionHandlers, $userData);
    }

    /**
     * @throws Throwable
     */
    protected function handleException(Throwable $exception): bool
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

        return $this->isHandled ?? throw $exception;
    }
}
