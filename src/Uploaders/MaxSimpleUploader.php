<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Uploaders;

use MaxMessenger\Bot\Exceptions\Uploaders\UnknownDataFormatException;
use MaxMessenger\Bot\Exceptions\Uploaders\UnknownErrorException;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Models\Enums\UploadType;
use MaxMessenger\Bot\Models\Responses\PhotoTokens;
use MaxMessenger\Bot\Models\Responses\UploadedFile;
use MaxMessenger\Bot\Uploaders\Contents\File;
use MaxMessenger\Bot\Uploaders\Contents\StringFile;

final readonly class MaxSimpleUploader
{
    public function __construct(
        private MaxApiClient $apiClient
    ) {
    }

    /**
     * @return non-empty-string
     */
    public function uploadAudio(File|StringFile $file): string
    {
        $meta = $this->apiClient->getUploadUrl(UploadType::Audio);

        $token = $meta->getToken() ?? throw new UnknownDataFormatException($meta->getRawData());

        $data = $this->apiClient->getHttpClient()->getUploadClient()
            ->postSimpleUpload($meta->getUrl(), $file, false);

        if ($data !== '<retval>1</retval>') {
            throw new UnknownErrorException($data);
        }

        return $token;
    }

    /**
     * @return non-empty-string
     */
    public function uploadFile(File|StringFile $file): string
    {
        return $this->uploadFileEx($file)->getToken();
    }

    public function uploadFileEx(File|StringFile $file): UploadedFile
    {
        $meta = $this->apiClient->getUploadUrl(UploadType::File);

        $data = $this->apiClient->getHttpClient()->getUploadClient()
            ->postSimpleUpload($meta->getUrl(), $file, true);

        return UploadedFile::newFromData($data);
    }

    /**
     * @return non-empty-string
     */
    public function uploadImage(File|StringFile $file): string
    {
        $photos = $this->uploadImageEx($file)->getPhotos();

        $item = reset($photos);

        /** @psalm-suppress DocblockTypeContradiction */
        if ($item === false || empty($token = $item->getToken())) {
            throw new UnknownDataFormatException($photos);
        }

        return $token;
    }

    public function uploadImageEx(File|StringFile $file): PhotoTokens
    {
        $meta = $this->apiClient->getUploadUrl(UploadType::Image);

        $data = $this->apiClient->getHttpClient()->getUploadClient()
            ->postSimpleUpload($meta->getUrl(), $file, true);

        $model = PhotoTokens::newFromData($data);
        if (!$model->isValid()) {
            throw new UnknownDataFormatException($data);
        }

        return $model;
    }

    /**
     * @return non-empty-string
     */
    public function uploadVideo(File|StringFile $file): string
    {
        $meta = $this->apiClient->getUploadUrl(UploadType::Video);

        $token = $meta->getToken() ?? throw new UnknownDataFormatException($meta->getRawData());

        $data = $this->apiClient->getHttpClient()->getUploadClient()
            ->postSimpleUpload($meta->getUrl(), $file, false);

        if ($data !== '<retval>1</retval>') {
            throw new UnknownErrorException($data);
        }

        return $token;
    }
}
