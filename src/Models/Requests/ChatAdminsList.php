<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use function array_key_exists;

/**
 * Список администраторов чата.
 */
final class ChatAdminsList extends BaseRequestModel
{
    use ValidateRequiredTrait;

    /**
     * @var array{
     *     admins: list<ChatAdmin>
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param ChatAdmin[]|null $admins Список пользователей, которые получат права администратора чата.
     */
    public function __construct(?array $admins = null)
    {
        $this->required = ['admins'];

        if ($admins !== null) {
            $this->setAdmins($admins);
        }
    }

    /**
     * @param ChatAdmin $admin
     * @return $this
     */
    public function addAdmin(ChatAdmin $admin): self
    {
        $this->data['admins'][] = $admin;

        return $this;
    }

    /**
     * @return list<ChatAdmin>
     */
    public function getAdmins(): array
    {
        return $this->data['admins'];
    }

    public function issetAdmins(): bool
    {
        return array_key_exists('admins', $this->data);
    }

    /**
     * @param ChatAdmin[] $admins Список пользователей, которые получат права администратора чата.
     */
    public static function make(array $admins): self
    {
        return new self($admins);
    }

    /**
     * @param ChatAdmin[]|null $admins Список пользователей, которые получат права администратора чата.
     */
    public static function new(?array $admins = null): self
    {
        return new self($admins);
    }

    /**
     * @param ChatAdmin[] $admins Список пользователей, которые получат права администратора чата.
     * @return $this
     */
    public function setAdmins(array $admins): self
    {
        $this->data['admins'] = array_values($admins);

        return $this;
    }
}
