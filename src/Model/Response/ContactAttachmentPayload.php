<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

use MaxMessenger\Bot\MaxApiClient;

class ContactAttachmentPayload extends BaseResponseModel
{
    /**
     * @var array{
     *     vcf_info?: non-empty-string,
     *     hash?: non-empty-string,
     *     max_info?: array
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private User|false|null $maxInfo = false;

    /**
     * @return non-empty-string|null Хеш информации о пользователе в формате VCF.
     *     Используется для проверки того, что пользователь поделился номером телефона,
     *     привязанным к его аккаунту в МАКС.
     * @see MaxApiClient::validateContactAttachmentHash()
     */
    public function getHash(): ?string
    {
        return $this->data['hash'] ?? null;
    }

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
     * @return string[]|null
     */
    public function getPhones(): ?array
    {
        $vcfInfo = str_replace("\r\n", "\n", $this->getVcfInfo() ?? '');

        if (preg_match_all('/^TEL.*:(\+?[\d ()\/.,;N-]+)$/mi', $vcfInfo, $matches) > 0) {
            return preg_replace('/[^0-9+]/', '', $matches[1]);
        }

        return null;
    }

    /**
     * @return non-empty-string|null Информация о пользователе в формате VCF (minLength: 20).
     */
    public function getVcfInfo(): ?string
    {
        return $this->data['vcf_info'] ?? null;
    }
}
