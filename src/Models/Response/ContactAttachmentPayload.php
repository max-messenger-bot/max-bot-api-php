<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

/**
 * Contact attachment payload.
 *
 * @api
 */
readonly class ContactAttachmentPayload extends BaseResponseModel
{
    /**
     * @var array{
     *     max_info?: array|null,
     *     vcf_info?: string|null
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @return User|null User info.
     * @api
     */
    public function getMaxInfo(): ?User
    {
        return User::newFromNullableData($this->data['max_info'] ?? null);
    }

    /**
     * @return string|null User info in VCF format.
     * @api
     */
    public function getVcfInfo(): ?string
    {
        return $this->data['vcf_info'] ?? null;
    }
}
