<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Exception\MaxBot\Event\PostIdMissingException;
use MaxMessenger\Bot\Exception\MaxBot\Event\SenderUnknownException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\ForbiddenException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\NotFoundException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\UnprocessableEntityException;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;
use MaxMessenger\Bot\Model\Enum\ChatType;
use MaxMessenger\Bot\Model\Request\NewCommentBody;
use MaxMessenger\Bot\Model\Request\NewMessageBody;
use MaxMessenger\Bot\Model\Response\CommentMessage;
use MaxMessenger\Bot\Model\Response\SendCommentResult;
use MaxMessenger\Bot\Model\Response\SendMessageResult;
use MaxMessenger\Bot\Model\Response\User;

use function is_string;

/**
 * Методы событий, связанных с комментариями к постам в каналах.
 *
 * Предоставляет доступ к комментарию события, его автору, каналу и посту, а также действия над комментарием:
 * ответ, редактирование и удаление.
 *
 * @psalm-require-extends BaseEvent
 * @property-read MaxApiClient $apiClient
 */
trait CommentEventTrait
{
    use SendMessageToUserTrait;

    /**
     * Удалить комментарий.
     *
     * Удаляет комментарий пользователя или бота: можно удалять как свои комментарии, так и чужие.
     *
     * Для удаления чужих комментариев бот должен быть администратором канала с правом
     * {@see ChatAdminPermission::Delete}.
     *
     * @throws ForbiddenException Если у бота нет прав на удаление комментария.
     * @throws PostIdMissingException Если в комментарии нет идентификатора поста.
     * @see MaxApiClient::deleteComment()
     */
    public function deleteComment(): void
    {
        $this->apiClient->deleteComment($this->getPostId(), $this->getComment()->getBody()->getMid());
    }

    /**
     * Редактировать комментарий.
     *
     * Бот может редактировать свои комментарии; комментарии, опубликованные от имени канала, —
     * только если ему назначено право администратора {@see ChatAdminPermission::Edit}.
     * Комментарий можно редактировать в течение 26 часов после публикации.
     *
     * @param NewCommentBody|non-empty-string $comment Тело нового комментария.
     * @throws PostIdMissingException Если в комментарии нет идентификатора поста.
     * @throws UnprocessableEntityException Если прошло больше 26 часов после публикации комментария
     *     (HTTP 422, код `comments.timeout_exceeded`).
     * @see MaxApiClient::editComment()
     */
    public function editComment(NewCommentBody|string $comment): void
    {
        $this->apiClient->editComment(
            $this->getPostId(),
            $this->getComment()->getBody()->getMid(),
            $comment,
        );
    }

    /**
     * @return int Идентификатор канала, в котором размещён пост с комментарием.
     */
    public function getChatId(): int
    {
        return $this->getComment()->getRecipient()->getChatId();
    }

    /**
     * @return CommentMessage Комментарий события.
     */
    abstract public function getComment(): CommentMessage;

    /**
     * @return non-empty-string Идентификатор поста в канале, к которому оставлен комментарий.
     * @throws PostIdMissingException Если в комментарии нет идентификатора поста.
     */
    public function getPostId(): string
    {
        return $this->getComment()->getRecipient()->getPostId() ?? throw new PostIdMissingException();
    }

    /**
     * @return User|null Пользователь, отправивший комментарий.
     *     Может быть `null`, если комментарий опубликован от имени канала.
     */
    public function getUser(): ?User
    {
        return $this->getComment()->getSender();
    }

    /**
     * @return int|null Идентификатор пользователя, отправившего комментарий.
     *     Может быть `null`, если комментарий опубликован от имени канала.
     */
    public function getUserId(): ?int
    {
        return $this->getUser()?->getUserId();
    }

    /**
     * @return bool `true`, если комментарий опубликован от имени канала.
     */
    public function isChannel(): bool
    {
        return $this->getComment()->getRecipient()->getChatType() === ChatType::Channel;
    }

    /**
     * @return bool `true`, если комментарий опубликован пользователем или ботом.
     */
    public function isChat(): bool
    {
        return $this->getComment()->getRecipient()->getChatType() === ChatType::Chat;
    }

    /**
     * Ответить комментарием к тому же посту.
     *
     * Для отправки комментария в настройках канала должны быть включены комментарии, а бот должен
     * быть администратором этого канала с правом {@see ChatAdminPermission::Write}.
     *
     * @param NewCommentBody|non-empty-string $comment Тело нового комментария.
     * @param bool $asReply Требуется ли ответить с цитированием комментария.
     * @return SendCommentResult Информация о созданном комментарии.
     * @throws NotFoundException Если пост удалён к моменту ответа.
     * @throws PostIdMissingException Если в комментарии нет идентификатора поста.
     * @see MaxApiClient::sendComment()
     */
    public function reply(NewCommentBody|string $comment, bool $asReply = false): SendCommentResult
    {
        if ($asReply) {
            if (is_string($comment)) {
                $comment = new NewCommentBody($comment);
            }
            $comment->setReplyLink($this->getComment());
        }

        return $this->apiClient->sendComment($this->getPostId(), $comment);
    }

    /**
     * Ответить на комментарий лично в диалоге с пользователем.
     *
     * Чтобы бот мог отправить сообщение пользователю, пользователь должен запустить бота.
     *
     * @param NewMessageBody|non-empty-string $message Тело нового сообщения.
     * @param bool $disableLinkPreview Если `true`, сервер не будет генерировать превью для ссылок в тексте сообщения.
     *     Параметр действует для этого сообщения, в том числе при его дальнейшем редактировании.
     * @return SendMessageResult Информация о созданном сообщении.
     * @throws SenderUnknownException Если комментарий опубликован от имени канала.
     * @see MaxApiClient::sendMessageToUser()
     */
    public function replyToUser(
        NewMessageBody|string $message,
        bool $disableLinkPreview = false,
    ): SendMessageResult {
        return $this->sendMessageToUser($message, $disableLinkPreview);
    }

    /**
     * @return User Пользователь, отправивший комментарий.
     * @throws SenderUnknownException Если комментарий опубликован от имени канала.
     */
    public function requireUser(): User
    {
        return $this->getUser() ?? throw new SenderUnknownException();
    }

    /**
     * @return int Идентификатор пользователя, отправившего комментарий.
     * @throws SenderUnknownException Если комментарий опубликован от имени канала.
     */
    public function requireUserId(): int
    {
        return $this->getUserId() ?? throw new SenderUnknownException();
    }
}
