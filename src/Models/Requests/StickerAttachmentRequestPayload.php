<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use function array_key_exists;

/**
 * Запрос на прикрепление стикера к сообщению.
 *
 * Должен быть единственным вложением в сообщении.
 */
final class StickerAttachmentRequestPayload extends BaseRequestModel
{
    use ValidateTrait;
    use ValidateRequiredTrait;

    /**
     * @var array{
     *     code: non-empty-string
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param non-empty-string|null $code Код стикера (minLength: 1).
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
     * @param non-empty-string $code Код стикера (minLength: 1).
     */
    public static function make(string $code): self
    {
        return new self($code);
    }

    /**
     * @param non-empty-string|null $code Код стикера (minLength: 1).
     */
    public static function new(?string $code = null): self
    {
        return new self($code);
    }

    /**
     * @param non-empty-string $code Код стикера (minLength: 1).
     * @return $this
     */
    public function setCode(string $code): self
    {
        self::validateString('code', $code, minLength: 1);

        $this->data['code'] = $code;

        return $this;
    }
}
