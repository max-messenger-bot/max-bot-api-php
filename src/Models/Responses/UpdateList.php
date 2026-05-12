<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Список событий в чатах и каналах, в которые добавлен бот.
 *
 * Обратите внимание, чтобы получать события из групповых чатов и каналов,
 * бот должен быть администратором
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
     * @return list<Update> Страница событий.
     */
    public function getUpdates(): array
    {
        return $this->updates === false
            ? ($this->updates = Update::newListFromData($this->data['updates']))
            : $this->updates;
    }
}
