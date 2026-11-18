<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

use function is_int;
use function is_string;

/**
 * Сведения о загруженном файле.
 *
 * @link https://dev.max.ru/docs-api/use-cases/sending-messages/media
 */
class UploadedFile extends BaseResponseModel
{
    /**
     * @var array{
     *     fileId: int,
     *     token: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return int Идентификатор загруженного файла.
     */
    public function getFileId(): int
    {
        return $this->data['fileId'];
    }

    /**
     * @return non-empty-string Токен загруженного файла.
     */
    public function getToken(): string
    {
        return $this->data['token'];
    }

    /**
     * @return bool Содержит ли ответ идентификатор файла и токен ожидаемых типов.
     */
    public function isValid(): bool
    {
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        return isset($this->data['fileId'], $this->data['token'])
            && is_int($this->data['fileId'])
            && is_string($this->data['token']);
    }
}
