<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

use function is_int;
use function is_string;

final class UploadedFile extends BaseResponseModel
{
    /**
     * @var array{
     *     fileId: int,
     *     token: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    public function getFileId(): int
    {
        return $this->data['fileId'];
    }

    /**
     * @return non-empty-string
     */
    public function getToken(): string
    {
        return $this->data['token'];
    }

    public function isValid(): bool
    {
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        return isset($this->data['fileId'], $this->data['token'])
            && is_int($this->data['fileId'])
            && is_string($this->data['token']);
    }
}
