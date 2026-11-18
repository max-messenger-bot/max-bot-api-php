<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

use function is_array;

/**
 * Информация о загруженных изображениях.
 *
 * @link https://dev.max.ru/docs-api/use-cases/sending-messages/media
 */
class PhotoTokens extends BaseResponseModel
{
    /**
     * @var array{
     *     photos: non-empty-array<string, array>
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    /**
     * @var non-empty-array<string, PhotoToken>|false
     */
    private array|false $photos = false;

    /**
     * @return non-empty-array<string, PhotoToken> Закодированная информация загруженных изображений.
     */
    public function getPhotos(): array
    {
        return $this->photos === false
            ? $this->photos = $this->preparePhotos()
            : $this->photos;
    }

    /**
     * @return bool Содержит ли ответ непустой набор загруженных изображений.
     */
    public function isValid(): bool
    {
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        return !empty($this->data['photos'])
            && is_array($this->data['photos']);
    }

    /**
     * @return non-empty-array<string, PhotoToken>
     */
    private function preparePhotos(): array
    {
        $photos = [];
        foreach ($this->data['photos'] as $key => $photoData) {
            $photos[$key] = PhotoToken::newFromData($photoData);
        }

        return $photos;
    }
}
