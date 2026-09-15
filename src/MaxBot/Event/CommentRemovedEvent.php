<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\NotFoundException;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Model\Request\NewCommentBody;
use MaxMessenger\Bot\Model\Response\CommentRemovedUpdate;
use MaxMessenger\Bot\Model\Response\SendCommentResult;

/**
 * Событие удаления комментария к посту в канале.
 *
 * На свои действия бот события не получает.
 *
 * @property-read CommentRemovedUpdate $update
 */
final class CommentRemovedEvent extends BaseEvent
{
    use SendMessageToUserTrait;

    /**
     * @return int ID чата, где комментарий был удалён.
     */
    public function getChatId(): int
    {
        return $this->update->getChatId();
    }

    /**
     * @return non-empty-string ID удалённого комментария.
     */
    public function getMessageId(): string
    {
        return $this->update->getMessageId();
    }

    /**
     * @return non-empty-string Идентификатор поста в канале.
     */
    public function getPostId(): string
    {
        return $this->update->getPostId();
    }

    public function getUser(): null
    {
        return null;
    }

    /**
     * @return int ID пользователя, удалившего комментарий.
     */
    public function getUserId(): int
    {
        return $this->update->getUserId();
    }

    /**
     * Ответить комментарием к тому же посту.
     *
     * Для отправки комментария в настройках канала должны быть включены комментарии, а бот должен
     * быть администратором этого канала с правами `read_all_messages` и `write`.
     *
     * - Вы можете получить ошибку {@see NotFoundException}, если пост на момент ответа будет удалён.
     *
     * @param NewCommentBody|non-empty-string $comment Тело нового комментария.
     * @return SendCommentResult Информация о созданном комментарии.
     * @see MaxApiClient::sendComment()
     */
    public function reply(NewCommentBody|string $comment): SendCommentResult
    {
        return $this->apiClient->sendComment($this->getPostId(), $comment);
    }
}
