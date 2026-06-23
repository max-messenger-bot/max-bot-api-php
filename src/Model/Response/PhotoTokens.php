<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

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
    /** @var non-empty-array<string, PhotoToken>|false */
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

    public function isValid(): bool
    {
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        return isset($this->data['photos'])
            && is_array($this->data['photos'])
            && $this->data['photos'] !== [];
    }

    /**
     * @return non-empty-array<string, PhotoToken>
     */
    private function preparePhotos(): array
    {
        $photos = $this->data['photos'];
        foreach ($photos as &$photoData) {
            $photoData = PhotoToken::newFromData($photoData);
        }
        unset($photoData);

        /** @var non-empty-array<string, PhotoToken> $photos */
        return $photos;
    }
}
