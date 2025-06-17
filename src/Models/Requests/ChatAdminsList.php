<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

/**
 * Список администраторов чата.
 *
 * @api
 */
final class ChatAdminsList extends BaseRequestModel
{
    /**
     * @var array{
     *     admins: list<ChatAdmin>
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType
     */
    protected array $data = [];

    /**
     * @param ChatAdmin[] $admins Список пользователей, которые получат права администратора чата.
     * @api
     */
    public function __construct(array $admins = [])
    {
        $this->setAdmins($admins);
    }

    /**
     * @param ChatAdmin $admin
     * @return $this
     * @api
     */
    public function addAdmin(ChatAdmin $admin): static
    {
        $this->data['admins'][] = $admin;

        return $this;
    }

    /**
     * @return list<ChatAdmin>
     * @api
     */
    public function getAdmins(): array
    {
        return $this->data['admins'];
    }

    /**
     * @param ChatAdmin[] $admins Список пользователей, которые получат права администратора чата.
     * @noinspection PhpMissingParentCallCommonInspection
     * @api
     */
    public static function new(array $admins = []): static
    {
        return new static($admins);
    }

    /**
     * @param ChatAdmin[] $admins Список пользователей, которые получат права администратора чата.
     * @return $this
     * @api
     */
    public function setAdmins(array $admins): static
    {
        $this->data['admins'] = array_values($admins);

        return $this;
    }
}
