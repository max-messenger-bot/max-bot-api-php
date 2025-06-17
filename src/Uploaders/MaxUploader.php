<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Uploaders;

use Closure;
use MaxMessenger\Bot\Contracts\Uploaders\StreamInterface;
use MaxMessenger\Bot\Exceptions\Uploaders\UnknownDataFormatException;
use MaxMessenger\Bot\Exceptions\Uploaders\UnknownErrorException;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Models\Enums\UploadType;
use MaxMessenger\Bot\Models\Responses\PhotoTokens;
use MaxMessenger\Bot\Models\Responses\UploadedFile;
use MaxMessenger\Bot\Models\Responses\UploadEndpoint;
use MaxMessenger\Bot\Uploaders\Contents\File;
use MaxMessenger\Bot\Uploaders\Contents\Stream;

use function is_string;

final class MaxUploader
{
    public bool $dynamicFragmentSize = true;
    /**
     * @var positive-int
     */
    public int $minFragmentLength = 256 * 1024;
    /**
     * @var (Closure(non-empty-string $postName, FragmentUploadStat $stat): void)|null
     */
    public ?Closure $progressCallback = null;
    /**
     * @var positive-int
     */
    public int $secondsForFragment = 5;
    /**
     * @var positive-int
     */
    public int $startFragmentLength = 5 * 1024 * 1024;
    private ?UploadEndpoint $lastMeta = null;

    public function __construct(
        private readonly MaxApiClient $apiClient
    ) {
    }

    public function getLastMeta(): ?UploadEndpoint
    {
        return $this->lastMeta;
    }

    /**
     * @return (Closure(non-empty-string $postName, FragmentUploadStat $stat): void)|null
     */
    public function getProgressCallback(): ?Closure
    {
        return $this->progressCallback;
    }

    /**
     * @return positive-int
     */
    public function getSecondsForFragment(): int
    {
        return $this->secondsForFragment;
    }

    /**
     * @return positive-int
     */
    public function getStartFragmentLength(): int
    {
        return $this->startFragmentLength;
    }

    public function isDynamicFragmentSize(): bool
    {
        return $this->dynamicFragmentSize;
    }

    /**
     * @return $this
     */
    public function setDynamicFragmentSize(bool $dynamicFragmentSize): self
    {
        $this->dynamicFragmentSize = $dynamicFragmentSize;

        return $this;
    }

    /**
     * @param (Closure(non-empty-string $postName, FragmentUploadStat $stat): void)|null $progressCallback
     * @return $this
     */
    public function setProgressCallback(?Closure $progressCallback): self
    {
        $this->progressCallback = $progressCallback;

        return $this;
    }

    /**
     * @param positive-int $secondsForFragment
     * @return $this
     */
    public function setSecondsForFragment(int $secondsForFragment): self
    {
        $this->secondsForFragment = $secondsForFragment;

        return $this;
    }

    /**
     * @param positive-int $startFragmentLength
     * @return $this
     */
    public function setStartFragmentLength(int $startFragmentLength): self
    {
        $this->startFragmentLength = $startFragmentLength;

        return $this;
    }

    /**
     * @param File|StreamInterface|non-empty-string $content
     * @return non-empty-string
     */
    public function uploadAudio(File|StreamInterface|string $content, ?UploadEndpoint $meta = null): string
    {
        return $this->uploadEx($content, UploadType::Audio, $meta, false);
    }

    /**
     * @param File|StreamInterface|non-empty-string $content
     * @return non-empty-string
     */
    public function uploadFile(File|StreamInterface|string $content, ?UploadEndpoint $meta = null): string
    {
        return $this->uploadFileEx($content, $meta)->getToken();
    }

    /**
     * @param File|StreamInterface|non-empty-string $content
     */
    public function uploadFileEx(File|StreamInterface|string $content, ?UploadEndpoint $meta = null): UploadedFile
    {
        return UploadedFile::newFromData($this->uploadEx($content, UploadType::File, $meta, true));
    }

    /**
     * @param File|StreamInterface|non-empty-string $content
     * @return non-empty-string
     */
    public function uploadImage(File|StreamInterface|string $content, ?UploadEndpoint $meta = null): string
    {
        $photos = $this->uploadImageEx($content, $meta)->getPhotos();

        $item = reset($photos);

        /** @psalm-suppress DocblockTypeContradiction */
        if ($item === false || empty($token = $item->getToken())) {
            throw new UnknownDataFormatException($photos);
        }

        return $token;
    }

    /**
     * @param File|StreamInterface|non-empty-string $content
     */
    public function uploadImageEx(File|StreamInterface|string $content, ?UploadEndpoint $meta = null): PhotoTokens
    {
        $data = $this->uploadEx($content, UploadType::Image, $meta, true);

        $model = PhotoTokens::newFromData($data);
        if (!$model->isValid()) {
            throw new UnknownDataFormatException($data);
        }

        return $model;
    }

    /**
     * @param File|StreamInterface|non-empty-string $content
     * @return non-empty-string
     */
    public function uploadVideo(File|StreamInterface|string $content, ?UploadEndpoint $meta = null): string
    {
        return $this->uploadEx($content, UploadType::Video, $meta, false);
    }

    /**
     * @param File|StreamInterface|non-empty-string $content
     */
    private function convertContentToStream(StreamInterface|File|string $content): StreamInterface
    {
        if (is_string($content)) {
            return Stream::fromFile(new File($content));
        }
        if ($content instanceof File) {
            return Stream::fromFile($content);
        }

        return $content;
    }

    /**
     * @template T of bool
     * @param File|StreamInterface|non-empty-string $content
     * @param T $json
     * @return (T is true ? array : non-empty-string)
     */
    private function uploadEx(
        File|StreamInterface|string $content,
        UploadType $type,
        ?UploadEndpoint $meta,
        bool $json
    ): array|string {
        $this->lastMeta = null;
        $stream = $this->convertContentToStream($content);
        $postName = $stream->getPostName();
        $size = $stream->getSize();
        // Размер фрагмента не может быть меньше 1/1000 длины файла или потока,
        // так как количество фрагментов не может быть больше 1000
        $minFragmentLength = max($this->minFragmentLength, (int)($size / 900));
        $currentLength = max($this->startFragmentLength, $minFragmentLength);
        $offset = 0;

        if ($meta !== null) {
            $uploadInfo = $this->apiClient->getHttpClient()->getUploadClient()
                ->getResumableUploadInfo($meta->getUrl());
            if ($uploadInfo !== null && $uploadInfo[1] === $size) {
                /** @var positive-int $offset */
                $offset = $uploadInfo[0] + 1;
            } else {
                $meta = null;
            }
        }
        if ($meta === null) {
            $meta = $this->apiClient->getUploadUrl($type);
        }
        $this->lastMeta = $meta;

        if (!$json) {
            $token = $meta->getToken() ?? throw new UnknownDataFormatException($meta->getRawData());
        }

        do {
            $length = max(min($size - $offset, $currentLength), 0);

            $time = microtime(true);
            $data = $this->apiClient->getHttpClient()->getUploadClient()
                ->postResumableUpload($meta->getUrl(), $stream, $offset, $length, $json);
            $time = microtime(true) - $time;

            if (!$json) {
                $pos = $offset + $length - 1;
                if ($data !== "0-$pos/$size") {
                    throw new UnknownErrorException($data);
                }
            }

            if ($this->progressCallback) {
                $stat = new FragmentUploadStat($offset, $length, $size, $time);
                ($this->progressCallback)($postName, $stat);
            }

            $offset += $length;

            /** @psalm-suppress InvalidOperand Psalm bug */
            if ($offset < $size && $this->dynamicFragmentSize) {
                if ($time < ($this->secondsForFragment * 0.9)) {
                    // Увеличение размера фрагмента
                    $currentLength = (int)($currentLength * ($this->secondsForFragment / $time));
                } elseif ($time > ($this->secondsForFragment * 1.2)) {
                    // Уменьшение размера фрагмента
                    $currentLength = (int)($currentLength * ($this->secondsForFragment / $time));
                    $currentLength = max($currentLength, $minFragmentLength);
                }
            }
        } while ($offset < $size);

        /**
         * @psalm-suppress PossiblyUndefinedVariable
         * @var (T is true ? array : non-empty-string)
         */
        return $json ? $data : $token;
    }
}
