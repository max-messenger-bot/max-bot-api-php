<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

class ContactAttachmentPayload extends BaseResponseModel
{
    /**
     * @var array{
     *     vcf_info?: non-empty-string,
     *     max_info?: array
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private User|false|null $maxInfo = false;

    /**
     * @return User|null Информация о пользователе.
     */
    public function getMaxInfo(): ?User
    {
        return $this->maxInfo === false
            ? ($this->maxInfo = User::newFromNullableData($this->data['max_info'] ?? null))
            : $this->maxInfo;
    }

    /**
     * @return non-empty-string|null Информация о пользователе в формате VCF (minLength: 20).
     */
    public function getVcfInfo(): ?string
    {
        return $this->data['vcf_info'] ?? null;
    }
}
