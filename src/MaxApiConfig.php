<?php

declare(strict_types=1);

namespace MaxMessenger\Bot;

use MaxMessenger\Bot\Contract\MaxApiConfigInterface;
use Mj4444\SimpleHttpClient\Contracts\HttpClientInterface;
use Mj4444\SimpleHttpClient\CurlHttpClient;
use SensitiveParameter;
use SensitiveParameterValue;

use function dirname;

use const CURLOPT_CAINFO;
use const CURLOPT_CAPATH;
use const CURLOPT_SSL_VERIFYHOST;
use const CURLOPT_SSL_VERIFYPEER;

final class MaxApiConfig implements MaxApiConfigInterface
{
    /**
     * Path to a directory holding custom CA certificates used to verify the server (curl `CAPATH`).
     *
     * Applies only to the default HTTP client. If you inject your own `httpClient`, configure TLS there.
     *
     * @var non-empty-string|null
     */
    public ?string $caCertificateDir = null;
    /**
     * Path to a custom CA certificate bundle used to verify the server (curl `CAINFO`).
     *
     * Use it to trust a private root CA — for example, the Минцифры root certificate that API Max relies on.
     * Applies only to the default HTTP client. If you inject your own `httpClient`, configure TLS there.
     *
     * @var non-empty-string|null
     */
    public ?string $caCertificatePath = null;
    /**
     * @var non-negative-int The number of **milliseconds** to wait while trying to connect.
     *     Use `0` to wait indefinitely.
     */
    public int $connectTimeout = 5000;
    /**
     * @var list<positive-int> Time before retry in milliseconds.
     */
    public array $retryAttempts = [1000, 2000, 4000, 8000, 15000];
    /**
     * @var non-negative-int The maximum number of **milliseconds** that a request can run.
     *     Use `0` to wait indefinitely.
     */
    public int $timeout = 10000;
    /**
     * Whether to verify the server's TLS certificate.
     *
     * Set to `false` to disable verification. This is insecure and intended only for local testing —
     * do not use it in production. Applies only to the default HTTP client.
     *
     * @var bool
     */
    public bool $verifySslCertificate = true;
    /**
     * @var SensitiveParameterValue<non-empty-string>|null
     */
    private ?SensitiveParameterValue $accessToken = null;

    /**
     * @param non-empty-string|null $accessToken
     * @param non-empty-string $baseUrl
     */
    public function __construct(
        #[SensitiveParameter]
        ?string $accessToken = null,
        public ?HttpClientInterface $httpClient = null,
        public string $baseUrl = 'https://platform-api2.max.ru',
    ) {
        $this->setAccessToken($accessToken);
    }

    public function getAccessToken(): ?SensitiveParameterValue
    {
        return $this->accessToken;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * @return non-empty-string|null Path to a directory holding custom CA certificates (curl `CAPATH`).
     */
    public function getCaCertificateDir(): ?string
    {
        return $this->caCertificateDir;
    }

    /**
     * @return non-empty-string|null Path to a custom CA certificate bundle (curl `CAINFO`).
     */
    public function getCaCertificatePath(): ?string
    {
        return $this->caCertificatePath;
    }

    /**
     * @return non-negative-int The number of **milliseconds** to wait while trying to connect.
     *     Use `0` to wait indefinitely.
     */
    public function getConnectTimeout(): int
    {
        return $this->connectTimeout;
    }

    public function getHttpClient(): HttpClientInterface
    {
        return $this->httpClient ??= $this->makeHttpClient();
    }

    public function getMaxHttpClient(): null
    {
        return null;
    }

    public function getRetryAttempts(): array
    {
        return $this->retryAttempts;
    }

    /**
     * @return non-negative-int The maximum number of **milliseconds** that a request can run.
     *     Use `0` to wait indefinitely.
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function getVerifySslCertificate(): bool
    {
        return $this->verifySslCertificate;
    }

    /**
     * Builds the default HTTP client and applies the configured timeouts and TLS options.
     */
    private function makeHttpClient(): CurlHttpClient
    {
        $httpClient = (new CurlHttpClient())
            ->setConnectTimeout($this->connectTimeout)
            ->setTimeout($this->timeout)
            ->setUserAgent('mj4444-MaxMessenger-Bot');

        if (!$this->verifySslCertificate) {
            $httpClient->setOption(CURLOPT_SSL_VERIFYPEER, false)
                ->setOption(CURLOPT_SSL_VERIFYHOST, 0);
        }

        if ($this->caCertificatePath !== null) {
            $httpClient->setOption(CURLOPT_CAINFO, $this->caCertificatePath);
        }

        if ($this->caCertificateDir !== null) {
            $httpClient->setOption(CURLOPT_CAPATH, $this->caCertificateDir);
        }

        return $httpClient;
    }

    /**
     * @param non-empty-string|null $accessToken API Max access token.
     * @return $this
     */
    public function setAccessToken(#[SensitiveParameter] ?string $accessToken): self
    {
        $this->accessToken = $accessToken !== null ? new SensitiveParameterValue($accessToken) : null;

        return $this;
    }

    /**
     * @param non-empty-string $baseUrl Base URL of API Max (example: `https://platform-api2.max.ru`).
     * @return $this
     */
    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * Sets a directory holding custom CA certificates used to verify the server (curl `CAPATH`).
     *
     * Applies only to the default HTTP client.
     *
     * @param non-empty-string|null $caCertificateDir Path to the CA certificates directory.
     * @return $this
     */
    public function setCaCertificateDir(?string $caCertificateDir): self
    {
        $this->caCertificateDir = $caCertificateDir;

        return $this;
    }

    /**
     * Sets a custom CA certificate bundle used to verify the server (curl `CAINFO`).
     *
     * Use it to trust a private root CA — for example, the Минцифры root certificate that API Max relies on.
     * Applies only to the default HTTP client.
     *
     * @param non-empty-string|null $caCertificatePath Path to the CA certificate bundle file.
     * @return $this
     */
    public function setCaCertificatePath(?string $caCertificatePath): self
    {
        $this->caCertificatePath = $caCertificatePath;

        return $this;
    }

    /**
     * Sets the connection timeout.
     *
     * @param non-negative-int $connectTimeout The number of **milliseconds** to wait while trying to connect.
     *     Use `0` to wait indefinitely.
     * @return $this
     */
    public function setConnectTimeout(int $connectTimeout): self
    {
        $this->connectTimeout = $connectTimeout;

        return $this;
    }

    /**
     * @param HttpClientInterface|null $httpClient HTTP client for API requests.
     * @return $this
     */
    public function setHttpClient(?HttpClientInterface $httpClient): self
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * @param list<positive-int> $retryAttempts Time before retry in milliseconds.
     * @return $this
     */
    public function setRetryAttempts(array $retryAttempts): self
    {
        $this->retryAttempts = $retryAttempts;

        return $this;
    }

    /**
     * Sets the request timeout.
     *
     * @param non-negative-int $timeout The maximum number of **milliseconds** that a request can run.
     *     Use `0` to wait indefinitely.
     * @return $this
     */
    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Enables or disables verification of the server's TLS certificate.
     *
     * Disabling verification is insecure and intended only for local testing — do not use it in production.
     * Applies only to the default HTTP client.
     *
     * @param bool $verifySslCertificate `false` to disable certificate verification.
     * @return $this
     */
    public function setVerifySslCertificate(bool $verifySslCertificate): self
    {
        $this->verifySslCertificate = $verifySslCertificate;

        return $this;
    }

    /**
     * Trusts the Russian Trusted CA certificates (Минцифры) bundled with the package.
     *
     * Points {@see caCertificatePath} at the CA bundle shipped in `resources/certs`. Use it when
     * API Max serves a certificate issued by the Минцифры CA that is missing from the system trust store.
     *
     * @return $this
     */
    public function useRussianTrustedCaCertificates(): self
    {
        $this->caCertificatePath = dirname(__DIR__) . '/resources/certs/russian_trusted_ca_bundle.pem';

        return $this;
    }
}
