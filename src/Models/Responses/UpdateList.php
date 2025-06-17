<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Список всех обновлений в чатах, в которых ваш бот участвовал.
 */
class UpdateList extends BaseResponseModel
{
    /**
     * @var array{
     *     updates: list<array>,
     *     marker?: int
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    /**
     * @var list<Update>|false
     */
    private array|false $updates = false;

    /**
     * @return int|null Указатель на следующую страницу данных.
     */
    public function getMarker(): ?int
    {
        return $this->data['marker'] ?? null;
    }

    /**
     * @return list<Update> Страница обновлений.
     */
    public function getUpdates(): array
    {
        return $this->updates === false
            ? ($this->updates = Update::newListFromData($this->data['updates']))
            : $this->updates;
    }
}
