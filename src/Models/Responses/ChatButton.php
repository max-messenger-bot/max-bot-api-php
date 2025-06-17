<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Кнопка создания чата.
 *
 * Кнопка, которая создает новый чат, как только первый пользователь на нее нажмёт.
 *
 * Бот будет добавлен в участники чата как администратор.
 *
 * Автор сообщения станет владельцем чата.
 */
class ChatButton extends Button
{
    /**
     * @var array{
     *     chat_title: non-empty-string,
     *     chat_description?: non-empty-string,
     *     start_payload?: non-empty-string,
     *     uuid?: int
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return non-empty-string|null Описание чата (minLength: 1, maxLength: 400).
     */
    public function getChatDescription(): ?string
    {
        return $this->data['chat_description'] ?? null;
    }

    /**
     * @return non-empty-string Название чата, который будет создан (minLength: 1, maxLength: 200).
     */
    public function getChatTitle(): string
    {
        return $this->data['chat_title'];
    }

    /**
     * @return non-empty-string|null Стартовая полезная нагрузка будет отправлена боту,
     *     как только чат будет создан (minLength: 1, maxLength: 512).
     */
    public function getStartPayload(): ?string
    {
        return $this->data['start_payload'] ?? null;
    }

    /**
     * @return int|null Уникальный ID кнопки среди всех кнопок чата на клавиатуре.
     *
     * Если `uuid` изменён, новый чат будет создан при следующем нажатии.
     *
     * Сервер сгенерирует его в момент, когда кнопка будет впервые размещена.
     *
     * Используйте его при редактировании сообщения.
     */
    public function getUuid(): ?int
    {
        return $this->data['uuid'] ?? null;
    }
}
