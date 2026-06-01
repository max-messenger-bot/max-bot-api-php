<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use ArrayObject;
use Closure;
use DateTimeImmutable;
use MaxMessenger\Bot\Exception\MaxBot\Event\EventException;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot\HandlerListType;
use MaxMessenger\Bot\Model\Enum\SenderAction;
use MaxMessenger\Bot\Model\Response\BotAddedToChatUpdate;
use MaxMessenger\Bot\Model\Response\BotRemovedFromChatUpdate;
use MaxMessenger\Bot\Model\Response\BotStartedUpdate;
use MaxMessenger\Bot\Model\Response\BotStoppedUpdate;
use MaxMessenger\Bot\Model\Response\ChatTitleChangedUpdate;
use MaxMessenger\Bot\Model\Response\DialogClearedUpdate;
use MaxMessenger\Bot\Model\Response\DialogMutedUpdate;
use MaxMessenger\Bot\Model\Response\DialogRemovedUpdate;
use MaxMessenger\Bot\Model\Response\DialogUnmutedUpdate;
use MaxMessenger\Bot\Model\Response\MessageCallbackUpdate;
use MaxMessenger\Bot\Model\Response\MessageCreatedUpdate;
use MaxMessenger\Bot\Model\Response\MessageEditedUpdate;
use MaxMessenger\Bot\Model\Response\MessageRemovedUpdate;
use MaxMessenger\Bot\Model\Response\Update;
use MaxMessenger\Bot\Model\Response\User;
use MaxMessenger\Bot\Model\Response\UserAddedToChatUpdate;
use MaxMessenger\Bot\Model\Response\UserRemovedFromChatUpdate;
use Throwable;

use function is_bool;

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
    public ?bool $isHandled = null;

    /**
     * @param list<Closure(Throwable $exception, BaseEvent $event): (bool|void)> $exceptionHandlers
     */
    protected function __construct(
        public readonly Update $update,
        public readonly MaxApiClient $apiClient,
        private readonly array $exceptionHandlers,
        public ArrayObject $userData,
    ) {}

    /**
     * Прерывает обработку **события** или **исключения**, установить **событию** статус `обработано`.
     */
    public function break(): never
    {
        throw new EventException(true);
    }

    /**
     * Прерывает обработку **события** или **исключения**, установить **событию** статус `не обработано`.
     */
    public function continue(): never
    {
        throw new EventException(false);
    }

    /**
     * Прерывает обработку **события** или **исключения**, статус **события** не менять.
     */
    public function exit(): never
    {
        throw new EventException();
    }

    abstract public function getChatId(): ?int;

    /**
     * @return DateTimeImmutable Время, когда произошло событие.
     */
    public function getTimestamp(): DateTimeImmutable
    {
        return $this->update->getTimestamp();
    }

    /**
     * @return int Время, когда произошло событие (Unix-время в миллисекундах).
     */
    public function getTimestampRaw(): int
    {
        return $this->update->getTimestampRaw();
    }

    abstract public function getUser(): ?User;

    abstract public function getUserId(): ?int;

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
            $this->handleException($exception);
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
     * @param list<Closure(Throwable $exception, BaseEvent $event): void> $exceptionHandlers Обработчики исключений.
     * @param ArrayObject $userData Массив для хранения любых пользовательских данных.
     */
    public static function new(
        Update $update,
        MaxApiClient $maxApiClient,
        array $exceptionHandlers,
        ArrayObject $userData = new ArrayObject(),
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

    public function sendAction(SenderAction $action): bool
    {
        $chatId = $this->getChatId();

        if ($chatId === null) {
            return false;
        }

        $this->apiClient->sendAction($chatId, $action);

        return true;
    }

    /**
     * Обрабатывает возникшее исключение.
     *
     * Вызывается при возникновении необработанного исключения в процессе обработки события.
     * Запускает все зарегистрированные обработчики исключений.
     *
     * Если ни один обработчик не вернул `true` или `false` и не вызвал соответствующие методы `Event`,
     * исключение будет выброшено дальше.
     *
     * @param Throwable $exception Обрабатываемое исключение.
     */
    protected function handleException(Throwable $exception): void
    {
        if (!$this->exceptionHandlers) {
            throw $exception;
        }

        $saveIsHandled = $this->isHandled;
        $newIsHandled = $saveIsHandled;
        $exceptionIsHandled = false;
        try {
            foreach ($this->exceptionHandlers as $exceptionHandler) {
                $this->isHandled = $saveIsHandled;
                try {
                    $result = $exceptionHandler($exception, $this);
                } catch (EventException $eventException) {
                    $result = $eventException->isHandled;
                }

                if (is_bool($result)) {
                    $exceptionIsHandled = true;
                    $newIsHandled = $newIsHandled || $result;
                }
            }
        } finally {
            $this->isHandled = $newIsHandled;
        }

        if (!$exceptionIsHandled) {
            throw $exception;
        }
    }
}
