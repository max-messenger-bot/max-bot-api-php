<?php

declare(strict_types=1);

namespace MaxMessenger\Api\Old\Sections;

use MaxMessenger\Api\Modules\ModuleTrait;
use MaxMessenger\Api\Old\Models\Enums\TextFormat;
use MaxMessenger\Api\Old\Models\Request\Messages\AttachmentRequest;
use MaxMessenger\Api\Old\Models\Request\Messages\NewMessage;
use MaxMessenger\Api\Old\Models\Request\Messages\NewMessageLink;
use MaxMessenger\Api\Old\Models\Response\Shared\Message;

/**
 * @api
 */
final class MaxMessages
{
    use ModuleTrait;

    /**
     * Отправить сообщение
     *
     * Отправляет сообщение в чат.
     *
     * Медиафайлы прикрепляются к сообщениям поэтапно:
     *
     * 1. Получите URL для загрузки медиафайлов.
     * 2. Загрузите бинарные данные соответствующего формата по полученному URL.
     * 3. После успешной загрузки получите объект в ответе. Используйте этот объект для создания вложения.
     *
     * @param int|null $userId Если вы хотите отправить сообщение пользователю, укажите его ID.
     * @param int|null $chatId Если сообщение отправляется в чат, укажите его ID.
     * @param string|null $text Новый текст сообщения. До 4000 символов.
     * @param AttachmentRequest|AttachmentRequest[]|null $attachments Вложения сообщения. Если пусто, все вложения будут удалены.
     * @param NewMessageLink|null $link Ссылка на сообщение.
     * @param bool|null $notify Если `false`, участники чата не будут уведомлены (по умолчанию `true`).
     * @param TextFormat|string|null $format Если установлен, текст сообщения будет форматрован данным способом.
     * @param bool|null $disableLinkPreview Если `false`, сервер не будет генерировать превью
     *                                      для ссылок в тексте сообщения.
     *
     * @link https://dev.max.ru/docs-api/methods/POST/messages
     */
    public function sendMessage(
        ?int $userId = null,
        ?int $chatId = null,
        ?string $text = null,
        AttachmentRequest|array|null $attachments = null,
        ?NewMessageLink $link = null,
        ?bool $notify = null,
        TextFormat|string|null $format = null,
        ?bool $disableLinkPreview = null
    ): Message {
        $request = new NewMessage($userId, $chatId, $text, $attachments, $link, $notify, $format, $disableLinkPreview);

        return new Message($this->post('/updates', $request));
    }
}
