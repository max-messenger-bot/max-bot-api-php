<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Кнопка-ссылка.
 *
 * После нажатия на такую кнопку пользователь переходит по ссылке, которую она содержит.
 *
 * @link https://dev.max.ru/docs-api/objects/Message/
 * @link https://dev.max.ru/docs-api/use-cases/sending-messages/keyboard
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
     * @return non-empty-string URL кнопки (maxLength: 2048).
     */
    public function getUrl(): string
    {
        return $this->data['url'];
    }
}
