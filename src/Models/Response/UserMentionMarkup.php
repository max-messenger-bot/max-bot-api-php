<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

/**
 * User mention markup.
 *
 * Represents user mention in text. Mention can be both by user's username or ID if user doesn't have username.
 *
 * @api
 */
readonly class UserMentionMarkup extends MarkupElement
{
    /**
     * @var array{
     *     user_link?: string|null,
     *     user_id?: int|null
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @return int|null Identifier of mentioned user without username.
     * @api
     */
    public function getUserId(): ?int
    {
        return $this->data['user_id'] ?? null;
    }

    /**
     * @return string|null `@username` of mentioned user.
     * @api
     */
    public function getUserLink(): ?string
    {
        return $this->data['user_link'] ?? null;
    }
}
