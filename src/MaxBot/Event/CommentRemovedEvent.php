<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Exception\MaxBot\Event\PostIdMissingException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\NotFoundException;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;
use MaxMessenger\Bot\Model\Request\NewCommentBody;
use MaxMessenger\Bot\Model\Response\CommentRemovedUpdate;
use MaxMessenger\Bot\Model\Response\SendCommentResult;

/**
 * Событие удаления комментария к посту в канале.
 *
 * Бот получает событие, только если он администратор канала с правом {@see ChatAdminPermission::ReadAllMessages}.
 *
 * @property-read CommentRemovedUpdate $update
 */
final class CommentRemovedEvent extends BaseEvent
{
    use SendMessageToUserTrait;

    /**
     * @return int Идентификатор чата, где комментарий был удалён.
     */
    public function getChatId(): int
    {
        return $this->update->getChatId();
    }

    /**
     * @return non-empty-string Идентификатор удалённого комментария.
     */
    public function getMessageId(): string
    {
        return $this->update->getMessageId();
    }

    /**
     * @return non-empty-string|null Идентификатор поста в канале, если он передан.
     */
    public function getPostId(): ?string
    {
        return $this->update->getPostId();
    }

    /**
     * @return null У события нет пользователя.
     */
    public function getUser(): null
    {
        return null;
    }

    /**
     * @return int Идентификатор пользователя, удалившего комментарий.
     */
    public function getUserId(): int
    {
        return $this->update->getUserId();
    }

    /**
     * Ответить комментарием к тому же посту.
     *
     * Для отправки комментария в настройках канала должны быть включены комментарии, а бот должен
     * быть администратором этого канала с правом {@see ChatAdminPermission::Write}.
     *
     * @param NewCommentBody|non-empty-string $comment Тело нового комментария.
     * @return SendCommentResult Информация о созданном комментарии.
     * @throws NotFoundException Если пост удалён к моменту ответа.
     * @throws PostIdMissingException Если в обновлении нет идентификатора поста.
     * @see MaxApiClient::sendComment()
     */
    public function reply(NewCommentBody|string $comment): SendCommentResult
    {
        $postId = $this->getPostId() ?? throw new PostIdMissingException();

        return $this->apiClient->sendComment($postId, $comment);
    }
}
