<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use function array_key_exists;

/**
 * @api
 */
final class ContactAttachmentRequestPayload extends BaseRequestModel
{
    use ValidateTrait;

    /**
     * @var array{
     *     contact_id?: int|null,
     *     name: non-empty-string|null,
     *     vcf_info?: non-empty-string|null,
     *     vcf_phone?: non-empty-string|null
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @param non-empty-string|null $name Contact name (minLength: 1).
     * @param int|null $contactId Contact identifier if it is registered Max user.
     * @param non-empty-string|null $vcfInfo Full information about contact in VCF format (minLength: 1).
     * @param non-empty-string|null $vcfPhone Contact phone in VCF format (minLength: 1).
     * @api
     */
    public function __construct(
        ?string $name,
        ?int $contactId = null,
        ?string $vcfInfo = null,
        ?string $vcfPhone = null
    ) {
        $this->setName($name);
        if ($contactId !== null) {
            $this->setContactId($contactId);
        }
        if ($vcfInfo !== null) {
            $this->setVcfInfo($vcfInfo);
        }
        if ($vcfPhone !== null) {
            $this->setVcfPhone($vcfPhone);
        }
    }

    /**
     * @api
     */
    public function getContactId(): ?int
    {
        return $this->data['contact_id'] ?? null;
    }

    /**
     * @return non-empty-string|null
     * @api
     */
    public function getName(): ?string
    {
        return $this->data['name'] ?? null;
    }

    /**
     * @return non-empty-string|null
     * @api
     */
    public function getVcfInfo(): ?string
    {
        return $this->data['vcf_info'] ?? null;
    }

    /**
     * @return non-empty-string|null
     * @api
     */
    public function getVcfPhone(): ?string
    {
        return $this->data['vcf_phone'] ?? null;
    }

    /**
     * @api
     */
    public function issetContactId(): bool
    {
        return array_key_exists('contact_id', $this->data);
    }

    /**
     * @api
     */
    public function issetVcfInfo(): bool
    {
        return array_key_exists('vcf_info', $this->data);
    }

    /**
     * @api
     */
    public function issetVcfPhone(): bool
    {
        return array_key_exists('vcf_phone', $this->data);
    }

    /**
     * @param non-empty-string|null $name Contact name (minLength: 1).
     * @param int|null $contactId Contact identifier if it is registered Max user.
     * @param non-empty-string|null $vcfInfo Full information about contact in VCF format (minLength: 1).
     * @param non-empty-string|null $vcfPhone Contact phone in VCF format (minLength: 1).
     * @api
     */
    public static function new(
        ?string $name = null,
        ?int $contactId = null,
        ?string $vcfInfo = null,
        ?string $vcfPhone = null
    ): static {
        static::validateNotNull('name', $name);

        return new static($name, $contactId, $vcfInfo, $vcfPhone);
    }

    /**
     * @param int|null $contactId Contact identifier if it is registered Max user.
     * @return $this
     * @api
     */
    public function setContactId(?int $contactId = null): static
    {
        $this->data['contact_id'] = $contactId;

        return $this;
    }

    /**
     * @param non-empty-string|null $name Contact name (minLength: 1).
     * @return $this
     * @api
     */
    public function setName(?string $name = null): static
    {
        static::validateString('name', $name, minLength: 1);

        $this->data['name'] = $name;

        return $this;
    }

    /**
     * @param non-empty-string|null $vcfInfo Full information about contact in VCF format (minLength: 1).
     * @return $this
     * @api
     */
    public function setVcfInfo(?string $vcfInfo = null): static
    {
        static::validateString('vcf_info', $vcfInfo, minLength: 1);

        $this->data['vcf_info'] = $vcfInfo;

        return $this;
    }

    /**
     * @param non-empty-string|null $vcfPhone Contact phone in VCF format (minLength: 1).
     * @return $this
     * @api
     */
    public function setVcfPhone(?string $vcfPhone = null): static
    {
        static::validateString('vcf_phone', $vcfPhone, minLength: 1);

        $this->data['vcf_phone'] = $vcfPhone;

        return $this;
    }

    /**
     * @return $this
     * @api
     */
    public function unsetContactId(): static
    {
        unset($this->data['contact_id']);

        return $this;
    }

    /**
     * @return $this
     * @api
     */
    public function unsetVcfInfo(): static
    {
        unset($this->data['vcf_info']);

        return $this;
    }

    /**
     * @return $this
     * @api
     */
    public function unsetVcfPhone(): static
    {
        unset($this->data['vcf_phone']);

        return $this;
    }
}
