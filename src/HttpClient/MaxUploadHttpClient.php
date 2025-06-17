<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient;

use MaxMessenger\Bot\Contracts\MaxApiConfigInterface;
use MaxMessenger\Bot\Contracts\Uploaders\StreamInterface;
use MaxMessenger\Bot\HttpClient\Exceptions\UnexpectedFormatException;
use MaxMessenger\Bot\HttpClient\Upload\ResumableInfoRequest;
use MaxMessenger\Bot\HttpClient\Upload\ResumableUploadRequest;
use MaxMessenger\Bot\HttpClient\Upload\SimpleUploadRequest;
use Mj4444\SimpleHttpClient\Contracts\HttpRequest\FileInterface;
use Mj4444\SimpleHttpClient\Contracts\HttpRequest\StringFileInterface;
use Mj4444\SimpleHttpClient\Contracts\HttpRequestInterface;
use Mj4444\SimpleHttpClient\Contracts\HttpResponseInterface;

use function is_array;

final readonly class MaxUploadHttpClient
{
    public function __construct(
        private MaxApiConfigInterface $config
    ) {
    }

    /**
     * @param non-empty-string $url
     * @return array{int, int}|null
     */
    public function getResumableUploadInfo(string $url): ?array
    {
        $response = $this->config->getHttpClient()->request(new ResumableInfoRequest($url));

        if ($response->getHttpCode() === 200) {
            $body = $response->getBody();
            if (preg_match('/^0-(\d+)\/(\d+)$/', $body, $matches)) {
                return [(int)$matches[1], (int)$matches[2]];
            }
        }

        return null;
    }

    /**
     * @template T of bool
     * @param non-empty-string $url
     * @param StreamInterface $stream
     * @param non-negative-int $offset
     * @param non-negative-int $length
     * @param T $json
     * @return (T is true ? array : string)
     */
    public function postResumableUpload(
        string $url,
        StreamInterface $stream,
        int $offset,
        int $length,
        bool $json
    ): array|string {
        $request = new ResumableUploadRequest($url, $stream, $offset, $length, $json);

        /** @psalm-suppress InvalidArgument Psalm bug */
        return $this->doRequest($request, $json);
    }

    /**
     * @template T of bool
     * @param non-empty-string $url
     * @param T $json
     * @return (T is true ? array : string)
     */
    public function postSimpleUpload(string $url, FileInterface|StringFileInterface $file, bool $json): array|string
    {
        $request = new SimpleUploadRequest($url, $file, $json);

        /** @psalm-suppress InvalidArgument Psalm bug */
        return $this->doRequest($request, $json);
    }

    /**
     * @template T of bool
     * @param T $json
     * @return (T is true ? array : string)
     */
    private function doRequest(HttpRequestInterface $request, bool $json): array|string
    {
        $response = $this->config->getHttpClient()->request($request);

        $response->checkHttpCode();

        if (!$json) {
            return $response->getBody();
        }

        $responseData = $response->getData();

        if (!is_array($responseData) || empty($responseData)) {
            /** @psalm-var HttpResponseInterface $response Psalm bug */
            throw new UnexpectedFormatException($response);
        }

        return $responseData;
    }
}
