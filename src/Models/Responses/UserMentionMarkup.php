<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Представляет упоминание пользователя в тексте.
 *
 * Упоминание может быть как по имени пользователя, так и по ID, если у пользователя нет имени.
 */
class UserMentionMarkup extends MarkupElement
{
    /**
     * @var array{
     *     user_link?: string,
     *     user_id?: int
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return int|null ID упомянутого пользователя без `@username`.
     */
    public function getUserId(): ?int
    {
        return $this->data['user_id'] ?? null;
    }

    /**
     * @return string|null `@username` упомянутого пользователя.
     */
    public function getUserLink(): ?string
    {
        return $this->data['user_link'] ?? null;
    }
}
