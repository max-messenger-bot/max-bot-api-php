<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

/**
 * Запрос на прикрепление стикера к сообщению.
 *
 * Должен быть единственным вложением в сообщении.
 *
 * @api
 */
final class StickerAttachmentRequestPayload extends BaseRequestModel
{
    use ValidateTrait;

    /**
     * @var array{
     *     code: non-empty-string
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType
     */
    protected array $data = [];

    /**
     * @param non-empty-string $code Код стикера (minLength: 1).
     * @api
     */
    public function __construct(string $code)
    {
        $this->setCode($code);
    }

    /**
     * @return non-empty-string
     * @api
     */
    public function getCode(): string
    {
        return $this->data['code'];
    }

    /**
     * @param non-empty-string|null $code Код стикера (minLength: 1).
     * @psalm-param non-empty-string $code
     * @noinspection PhpMissingParentCallCommonInspection
     * @api
     */
    public static function new(?string $code = null): static
    {
        static::validateNotNull('code', $code);

        return new static($code);
    }

    /**
     * @param non-empty-string $code Код стикера (minLength: 1).
     * @return $this
     * @api
     */
    public function setCode(string $code): static
    {
        static::validateString('code', $code, minLength: 1);

        $this->data['code'] = $code;

        return $this;
    }
}
