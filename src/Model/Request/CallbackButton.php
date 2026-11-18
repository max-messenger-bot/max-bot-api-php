<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Request;

use MaxMessenger\Bot\HttpClient\Exception\HttpRequest\JsonEncodeException;
use MaxMessenger\Bot\Model\Enum\ButtonType;
use Throwable;

use function array_key_exists;
use function is_array;
use function json_encode;

/**
 * Callback-кнопка.
 *
 * После нажатия на такую кнопку клиент отправляет на сервер её полезную нагрузку.
 *
 * @link https://dev.max.ru/docs-api/objects/NewMessageBody/
 * @link https://dev.max.ru/docs-api/methods/POST/messages/
 * @link https://dev.max.ru/docs-api/use-cases/sending-messages/keyboard
 */
final class CallbackButton extends Button
{
    /**
     * @var array{
     *     payload: non-empty-string
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param non-empty-string|null $text Видимый текст кнопки (maxLength: 128).
     * @param non-empty-string|array|null $payload Данные кнопки (maxLength: 1024).
     */
    public function __construct(?string $text = null, string|array|null $payload = null)
    {
        parent::__construct(ButtonType::Callback, $text);

        $this->required[] = 'payload';

        if ($payload !== null) {
            $this->setPayload($payload);
        }
    }

    /**
     * @return non-empty-string
     */
    public function getPayload(): string
    {
        return $this->data['payload'];
    }

    public function issetPayload(): bool
    {
        return array_key_exists('payload', $this->data);
    }

    /**
     * @param non-empty-string $text Видимый текст кнопки (maxLength: 128).
     * @param non-empty-string|array $payload Данные кнопки (maxLength: 1024).
     */
    public static function make(string $text, string|array $payload): self
    {
        return new self($text, $payload);
    }

    /**
     * @param non-empty-string|null $text Видимый текст кнопки (maxLength: 128).
     * @param non-empty-string|array|null $payload Данные кнопки (maxLength: 1024).
     */
    public static function new(?string $text = null, string|array|null $payload = null): self
    {
        return new self($text, $payload);
    }

    /**
     * @param non-empty-string|array $payload Данные кнопки (maxLength: 1024).
     * @return $this
     */
    public function setPayload(string|array $payload): self
    {
        if (is_array($payload)) {
            try {
                $payload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            } catch (Throwable $e) {
                /** @psalm-var array $payload */
                throw new JsonEncodeException($payload, $e);
            }
        }

        self::validateString('payload', $payload, minLength: 1, maxLength: 1024);

        $this->data['payload'] = $payload;

        return $this;
    }
}
