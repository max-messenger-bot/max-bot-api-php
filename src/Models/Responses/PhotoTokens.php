<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

use function is_array;

/**
 * Информация о загруженных изображениях.
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
     * @return non-empty-array<string, PhotoToken> Закодированная информация загруженных изображений.
     */
    public function getPhotos(): array
    {
        $photos = $this->data['photos'];
        foreach ($photos as &$photoData) {
            $photoData = PhotoToken::newFromData($photoData);
        }

        /** @var non-empty-array<string, PhotoToken> */
        return $photos;
    }

    public function isValid(): bool
    {
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        return isset($this->data['photos'])
            && is_array($this->data['photos'])
            && $this->data['photos'] !== [];
    }
}
