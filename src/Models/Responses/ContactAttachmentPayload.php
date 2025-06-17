<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Contact attachment payload.
 *
 * @api
 */
class ContactAttachmentPayload extends BaseResponseModel
{
    /**
     * @var array{
     *     max_info?: array|null,
     *     vcf_info?: string|null
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private User|false|null $maxInfo = false;

    /**
     * @return User|null User info.
     * @api
     */
    public function getMaxInfo(): ?User
    {
        return $this->maxInfo === false
            ? ($this->maxInfo = User::newFromNullableData($this->data['max_info'] ?? null))
            : $this->maxInfo;
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
