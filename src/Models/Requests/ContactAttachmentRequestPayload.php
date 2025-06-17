<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use function array_key_exists;

final class ContactAttachmentRequestPayload extends BaseRequestModel
{
    use ValidateTrait;
    use ValidateRequiredTrait;

    /**
     * @var array{
     *     contact_id?: int,
     *     vcf_info?: non-empty-string
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType
     */
    protected array $data = [];

    /**
     * @param int|null $contactId ID контакта, если он зарегистрирован в MAX.
     * @param non-empty-string|null $vcfInfo Полная информация о контакте в формате VCF (minLength: 20).
     */
    public function __construct(
        ?int $contactId = null,
        ?string $vcfInfo = null
    ) {
        $this->requiredOnce = ['contact_id', 'vcf_info'];

        if ($contactId !== null) {
            $this->setContactId($contactId);
        }
        if ($vcfInfo !== null) {
            $this->setVcfInfo($vcfInfo);
        }
    }

    public function getContactId(): ?int
    {
        return $this->data['contact_id'] ?? null;
    }

    /**
     * @return non-empty-string|null
     */
    public function getVcfInfo(): ?string
    {
        return $this->data['vcf_info'] ?? null;
    }

    public function issetContactId(): bool
    {
        return array_key_exists('contact_id', $this->data);
    }

    public function issetVcfInfo(): bool
    {
        return array_key_exists('vcf_info', $this->data);
    }

    /**
     * @param int|null $contactId ID контакта, если он зарегистрирован в MAX.
     * @param non-empty-string|null $vcfInfo Полная информация о контакте в формате VCF (minLength: 20).
     */
    public static function make(
        ?int $contactId = null,
        ?string $vcfInfo = null
    ): self {
        return new self($contactId, $vcfInfo);
    }

    /**
     * @param int|null $contactId ID контакта, если он зарегистрирован в MAX.
     * @param non-empty-string|null $vcfInfo Полная информация о контакте в формате VCF (minLength: 20).
     */
    public static function new(
        ?int $contactId = null,
        ?string $vcfInfo = null
    ): self {
        return new self($contactId, $vcfInfo);
    }

    /**
     * @param int $contactId ID контакта, если он зарегистрирован в MAX.
     * @return $this
     */
    public function setContactId(int $contactId): self
    {
        $this->data['contact_id'] = $contactId;

        return $this;
    }

    /**
     * @param non-empty-string $vcfInfo Полная информация о контакте в формате VCF (minLength: 20).
     * @return $this
     */
    public function setVcfInfo(string $vcfInfo): self
    {
        self::validateString('vcfInfo', $vcfInfo, minLength: 20);

        $this->data['vcf_info'] = $vcfInfo;

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetContactId(): self
    {
        unset($this->data['contact_id']);

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetVcfInfo(): self
    {
        unset($this->data['vcf_info']);

        return $this;
    }
}
