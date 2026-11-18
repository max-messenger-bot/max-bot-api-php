<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Request;

use function array_key_exists;

/**
 * Данные для прикрепления стикера к сообщению.
 *
 * @link https://dev.max.ru/docs-api/objects/NewMessageBody
 */
final class StickerAttachmentRequestPayload extends BaseRequestModel
{
    use ValidateRequiredTrait;
    use ValidateTrait;

    /**
     * @var array{
     *     code: non-empty-string
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param non-empty-string|null $code Код стикера.
     */
    public function __construct(?string $code = null)
    {
        $this->required = ['code'];

        if ($code !== null) {
            $this->setCode($code);
        }
    }

    /**
     * @return non-empty-string
     */
    public function getCode(): string
    {
        return $this->data['code'];
    }

    public function issetCode(): bool
    {
        return array_key_exists('code', $this->data);
    }

    /**
     * @param non-empty-string $code Код стикера.
     */
    public static function make(string $code): self
    {
        return new self($code);
    }

    /**
     * @param non-empty-string|null $code Код стикера.
     */
    public static function new(?string $code = null): self
    {
        return new self($code);
    }

    /**
     * @param non-empty-string $code Код стикера.
     * @return $this
     */
    public function setCode(string $code): self
    {
        self::validateString('code', $code, minLength: 1);

        $this->data['code'] = $code;

        return $this;
    }
}
