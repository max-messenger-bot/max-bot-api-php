<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Кнопка-ссылка.
 *
 * После нажатия на такую кнопку пользователь переходит по ссылке, которую она содержит.
 */
class LinkButton extends Button
{
    /**
     * @var array{
     *     url: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return non-empty-string URL кнопки (minLength: 4, maxLength: 2048).
     */
    public function getUrl(): string
    {
        return $this->data['url'];
    }
}
