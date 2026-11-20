<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use ArrayObject;
use Closure;
use DateTimeImmutable;
use MaxMessenger\Bot\Exception\MaxBot\Event\ChatIdMissingException;
use MaxMessenger\Bot\Exception\MaxBot\Event\EventException;
use MaxMessenger\Bot\Exception\MaxBot\Event\UserMissingException;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot\HandlerListType;
use MaxMessenger\Bot\Model\Response\BotAddedToChatUpdate;
use MaxMessenger\Bot\Model\Response\BotAdminPermissionsChangedUpdate;
use MaxMessenger\Bot\Model\Response\BotRemovedFromChatUpdate;
use MaxMessenger\Bot\Model\Response\BotStartedUpdate;
use MaxMessenger\Bot\Model\Response\BotStoppedUpdate;
use MaxMessenger\Bot\Model\Response\ChatTitleChangedUpdate;
use MaxMessenger\Bot\Model\Response\CommentCreatedUpdate;
use MaxMessenger\Bot\Model\Response\CommentEditedUpdate;
use MaxMessenger\Bot\Model\Response\CommentRemovedUpdate;
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
 * Базовый класс события бота.
 *
 * Хранит полученное обновление {@see Update}, API-клиент и пользовательские данные, а также управляет
 * статусом обработки события и вызовом обработчиков.
 *
 * @psalm-consistent-constructor
 */
abstract class BaseEvent
{
    /**
     * @var HandlerListType|null Текущий список, в котором обрабатывается событие.
     */
    public ?HandlerListType $currentHandlerListType = null;
    /**
     * @var HandlerListType|null Список, в котором событие было обработано.
     */
    public ?HandlerListType $handledIn = null;
    /**
     * @var bool|null Статус обработки события в рамках текущего списка.
     */
    public ?bool $isHandled = null;

    /**
     * Создаёт событие из обновления и контекста бота.
     *
     * @param Update $update Полученное обновление.
     * @param MaxApiClient $apiClient API-клиент бота.
     * @param list<Closure(Throwable $exception, BaseEvent $event): (bool|void)> $exceptionHandlers Обработчики
     *     исключений.
     * @param ArrayObject $userData Пользовательские данные события.
     */
    protected function __construct(
        public readonly Update $update,
        public readonly MaxApiClient $apiClient,
        private readonly array $exceptionHandlers,
        public ArrayObject $userData,
    ) {}

    /**
     * Прерывает обработку **события** или **исключения** и устанавливает **событию** статус `обработано`.
     */
    public function break(): never
    {
        throw new EventException(true);
    }

    /**
     * Прерывает обработку **события** или **исключения** и устанавливает **событию** статус `не обработано`.
     */
    public function continue(): never
    {
        throw new EventException(false);
    }

    /**
     * Прерывает обработку **события** или **исключения**, не меняя статус **события**.
     */
    public function exit(): never
    {
        throw new EventException();
    }

    /**
     * @return int|null Идентификатор чата события или `null`, если у события нет чата.
     */
    abstract public function getChatId(): ?int;

    /**
     * @return DateTimeImmutable Время, когда произошло событие.
     */
    public function getTimestamp(): DateTimeImmutable
    {
        return $this->update->getTimestamp();
    }

    /**
     * @return non-negative-int Время, когда произошло событие (Unix-время в миллисекундах).
     */
    public function getTimestampRaw(): int
    {
        return $this->update->getTimestampRaw();
    }

    /**
     * @return User|null Пользователь события или `null`, если у события нет пользователя.
     */
    abstract public function getUser(): ?User;

    /**
     * @return int|null Идентификатор пользователя события или `null`, если у события нет пользователя.
     */
    abstract public function getUserId(): ?int;

    /**
     * Обработать событие заданным обработчиком.
     *
     * @param Closure(static $event): (bool|void) $handler Обработчик события. При возврате `true`
     *     событие отмечается как обработанное, при обходе списка следующие обработчики не вызываются.
     *     Если обработчик завершился без возвращаемого значения, сохраняется установленный им статус,
     *     а при отсутствии явного изменения используется `$defaultIsHandled`.
     * @param bool|null $defaultIsHandled Статус события при завершении обработчика без возвращаемого значения.
     * @return bool `true`, если событие обработано и дальнейшая обработка должна быть остановлена.
     */
    public function handle(Closure $handler, ?bool $defaultIsHandled = true): bool
    {
        try {
            $this->isHandled = null;

            /** @psalm-suppress TypeDoesNotContainType Обработчик может изменить статус события. */
            $this->isHandled = $handler($this) ?? $this->isHandled ?? $defaultIsHandled;
        } catch (EventException $exception) {
            $this->isHandled = $exception->isHandled ?? $this->isHandled ?? $defaultIsHandled;
        } catch (Throwable $exception) {
            $this->handleException($exception);
        }

        return $this->isHandled === true;
    }

    /**
     * Отмечает событие обработанным.
     */
    public function markAsHandled(): void
    {
        $this->isHandled = true;
    }

    /**
     * Отмечает событие не обработанным.
     */
    public function markAsUnhandled(): void
    {
        $this->isHandled = false;
    }

    /**
     * Создаёт объект события, соответствующий типу обновления.
     *
     * @param Update $update Полученное обновление.
     * @param MaxApiClient $maxApiClient API-клиент бота.
     * @param list<Closure(Throwable $exception, BaseEvent $event): (bool|void)> $exceptionHandlers Обработчики
     *     исключений.
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
            BotAdminPermissionsChangedUpdate::class => BotAdminPermissionsChangedEvent::class,
            BotRemovedFromChatUpdate::class => BotRemovedFromChatEvent::class,
            BotStartedUpdate::class => BotStartedEvent::class,
            BotStoppedUpdate::class => BotStoppedEvent::class,
            ChatTitleChangedUpdate::class => ChatTitleChangedEvent::class,
            CommentCreatedUpdate::class => CommentCreatedEvent::class,
            CommentEditedUpdate::class => CommentEditedEvent::class,
            CommentRemovedUpdate::class => CommentRemovedEvent::class,
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

        $className = $classMap[$update::class] ?? UnknownEvent::class;

        return new $className($update, $maxApiClient, $exceptionHandlers, $userData);
    }

    /**
     * @return int Идентификатор чата события.
     * @throws ChatIdMissingException Если у события нет чата.
     */
    public function requireChatId(): int
    {
        return $this->getChatId() ?? throw new ChatIdMissingException();
    }

    /**
     * @return User Пользователь события.
     * @throws UserMissingException Если у события нет пользователя.
     */
    public function requireUser(): User
    {
        return $this->getUser() ?? throw new UserMissingException();
    }

    /**
     * @return int Идентификатор пользователя события.
     * @throws UserMissingException Если у события нет пользователя.
     */
    public function requireUserId(): int
    {
        return $this->getUserId() ?? throw new UserMissingException();
    }

    /**
     * Обрабатывает возникшее исключение.
     *
     * Вызывается при возникновении необработанного исключения в процессе обработки события.
     * Запускает все зарегистрированные обработчики исключений.
     *
     * Если ни один обработчик не вернул `true` или `false` и не вызвал соответствующие методы {@see Event},
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
