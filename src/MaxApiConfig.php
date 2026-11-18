<?php

declare(strict_types=1);

namespace MaxMessenger\Bot;

use MaxMessenger\Bot\Contract\Http\HttpClientInterface;
use MaxMessenger\Bot\Contract\MaxApiConfigInterface;
use MaxMessenger\Bot\CurlHttpClient\CurlHttpClient;
use MaxMessenger\Bot\HttpClient\MaxHttpClient;
use MaxMessenger\Bot\PsrHttpClient\PsrHttpClient;
use SensitiveParameter;
use SensitiveParameterValue;

use function dirname;

/**
 * Настройки подключения к API Max.
 *
 * Хранит токен доступа, базовый адрес API, HTTP-клиенты, задержки повторных попыток и параметры TLS.
 * Значения задаются в конструкторе, напрямую через публичные свойства или цепочкой методов `set*`.
 *
 * Если HTTP-клиент не задан, {@see getHttpClient()} создаёт {@see CurlHttpClient} с таймаутами и настройками
 * TLS из этого класса ({@see makeCurlHttpClient()}). Отдельный клиент для длинного опроса обновлений
 * необязателен: без него {@see MaxHttpClient} берёт основной клиент. Для загрузки медиафайлов без заданного клиента
 * {@see getHttpClientForUploader()} создаёт curl-клиент с системными сертификатами: серверы загрузки используют
 * сертификаты публичных удостоверяющих центров.
 *
 * Таймауты и параметры TLS относятся только к встроенному curl-клиенту. Собственный клиент, в том числе
 * {@see PsrHttpClient}, настраивается самостоятельно и эти значения игнорирует.
 *
 * Сертификат API Max выпущен удостоверяющим центром Минцифры, которого нет в большинстве системных хранилищ
 * доверия, поэтому для встроенного клиента может понадобиться {@see useRussianTrustedCaCertificates()}.
 *
 * Токен хранится в {@see SensitiveParameterValue}, чтобы не попасть в трассировку исключений и вывод отладки.
 */
final class MaxApiConfig implements MaxApiConfigInterface
{
    /**
     * @var non-empty-string Базовый адрес API Max.
     */
    public const BASE_URL = 'https://platform-api2.max.ru';

    /**
     * @var list<positive-int>|null Задержки перед повтором отправки при `attachment.not.ready`.
     *     `null` — использовать $retryAttempts, `[]` — не повторять отправку.
     */
    public ?array $attachmentRetryAttempts = null;
    /**
     * @var non-empty-string|null Путь к каталогу с собственными корневыми сертификатами для проверки
     *     сервера (curl `CAPATH`). Игнорируется в {@see PsrHttpClient}.
     */
    public ?string $caCertificateDir = null;
    /**
     * @var non-empty-string|null Путь к собственному набору корневых сертификатов для проверки
     *     сервера (curl `CAINFO`). Игнорируется в {@see PsrHttpClient}.
     */
    public ?string $caCertificatePath = null;
    /**
     * @var non-negative-int Сколько **миллисекунд** ждать подключения. `0` — ждать сколько угодно.
     *     Игнорируется в {@see PsrHttpClient}.
     */
    public int $connectTimeout = 5000;
    /**
     * @var HttpClientInterface|null HTTP-клиент для запросов к API.
     */
    public ?HttpClientInterface $httpClient = null;
    /**
     * @var HttpClientInterface|null HTTP-клиент для длинного опроса обновлений.
     */
    public ?HttpClientInterface $httpClientForUpdates = null;
    /**
     * @var HttpClientInterface|null HTTP-клиент для загрузки медиафайлов.
     */
    public ?HttpClientInterface $httpClientForUploader = null;
    /**
     * @var list<positive-int>|null Задержки повторов POST в миллисекундах.
     *     `null` — использовать $retryAttempts, `[]` — не повторять POST.
     */
    public ?array $postRetryAttempts = null;
    /**
     * @var list<positive-int> Задержка перед повторной попыткой в миллисекундах.
     */
    public array $retryAttempts = [1000, 2000, 4000, 8000, 15000];
    /**
     * @var non-negative-int Сколько **миллисекунд** может выполняться запрос. `0` — без ограничения.
     *     Игнорируется в {@see PsrHttpClient}.
     */
    public int $timeout = 10000;
    /**
     * @var bool `false` отключает проверку TLS-сертификата сервера.
     *     Это небезопасно и годится только для локальных проверок. Игнорируется в {@see PsrHttpClient}.
     */
    public bool $verifySslCertificate = true;
    /**
     * @var SensitiveParameterValue<non-empty-string>|null Токен доступа к API Max.
     */
    private ?SensitiveParameterValue $accessToken = null;

    /**
     * @param non-empty-string|null $accessToken Токен доступа к API Max.
     * @param bool $useRussianTrustedCaCertificates Доверять корневым сертификатам Минцифры, поставляемым с пакетом
     *     (см. {@see useRussianTrustedCaCertificates()}).
     * @param non-empty-string $baseUrl Базовый адрес API Max.
     */
    public function __construct(
        #[SensitiveParameter]
        ?string $accessToken = null,
        bool $useRussianTrustedCaCertificates = false,
        public string $baseUrl = self::BASE_URL,
    ) {
        $this->setAccessToken($accessToken);

        if ($useRussianTrustedCaCertificates) {
            $this->useRussianTrustedCaCertificates();
        }
    }

    /**
     * @return SensitiveParameterValue<non-empty-string>|null Токен доступа к API Max.
     */
    public function getAccessToken(): ?SensitiveParameterValue
    {
        return $this->accessToken;
    }

    /**
     * @return list<positive-int> Задержки перед повтором отправки при `attachment.not.ready`.
     */
    public function getAttachmentRetryAttempts(): array
    {
        return $this->attachmentRetryAttempts ?? $this->retryAttempts;
    }

    /**
     * @return non-empty-string Базовый адрес API Max.
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * @return non-empty-string|null Путь к каталогу с собственными корневыми сертификатами для проверки
     *     сервера (curl `CAPATH`). Игнорируется в {@see PsrHttpClient}.
     */
    public function getCaCertificateDir(): ?string
    {
        return $this->caCertificateDir;
    }

    /**
     * @return non-empty-string|null Путь к собственному набору корневых сертификатов для проверки
     *     сервера (curl `CAINFO`). Игнорируется в {@see PsrHttpClient}.
     */
    public function getCaCertificatePath(): ?string
    {
        return $this->caCertificatePath;
    }

    /**
     * @return non-negative-int Сколько **миллисекунд** ждать подключения. `0` — ждать сколько угодно.
     *     Игнорируется в {@see PsrHttpClient}.
     */
    public function getConnectTimeout(): int
    {
        return $this->connectTimeout;
    }

    /**
     * @return HttpClientInterface HTTP-клиент для запросов к API.
     */
    public function getHttpClient(): HttpClientInterface
    {
        return $this->httpClient ??= $this->makeCurlHttpClient();
    }

    /**
     * @return HttpClientInterface|null HTTP-клиент для длинного опроса обновлений.
     */
    public function getHttpClientForUpdates(): ?HttpClientInterface
    {
        return $this->httpClientForUpdates;
    }

    /**
     * Если клиент не задан, создаёт curl-клиент, проверяющий серверы загрузки по системным сертификатам
     * ({@see makeCurlHttpClient()}).
     *
     * @return HttpClientInterface HTTP-клиент для загрузки медиафайлов.
     */
    public function getHttpClientForUploader(): HttpClientInterface
    {
        return $this->httpClientForUploader ??= $this->makeCurlHttpClient(useSystemCaCertificates: true);
    }

    /**
     * @return list<positive-int> Задержки повторов POST в миллисекундах.
     */
    public function getPostRetryAttempts(): array
    {
        return $this->postRetryAttempts ?? $this->retryAttempts;
    }

    /**
     * @return list<positive-int> Задержка перед повторной попыткой в миллисекундах.
     */
    public function getRetryAttempts(): array
    {
        return $this->retryAttempts;
    }

    /**
     * @return non-negative-int Сколько **миллисекунд** может выполняться запрос. `0` — без ограничения.
     *     Игнорируется в {@see PsrHttpClient}.
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @return bool `false` отключает проверку TLS-сертификата сервера.
     *     Это небезопасно и годится только для локальных проверок. Игнорируется в {@see PsrHttpClient}.
     */
    public function getVerifySslCertificate(): bool
    {
        return $this->verifySslCertificate;
    }

    /**
     * Создаёт curl-клиент с заданными таймаутами и настройками TLS.
     *
     * Если это поддерживается libcurl, клиент также доверяет хранилищу сертификатов операционной системы.
     *
     * @param bool $useSystemCaCertificates `true` — проверять сервер только по системным сертификатам,
     *     без {@see caCertificatePath} и {@see caCertificateDir}.
     */
    public function makeCurlHttpClient(bool $useSystemCaCertificates = false): CurlHttpClient
    {
        return (new CurlHttpClient())
            ->setConnectTimeout($this->connectTimeout)
            ->setTimeout($this->timeout)
            ->setUserAgent('mj4444-MaxMessenger-Bot')
            ->setVerifySslCertificate($this->verifySslCertificate)
            ->setCaCertificatePath($useSystemCaCertificates ? null : $this->caCertificatePath)
            ->setCaCertificateDir($useSystemCaCertificates ? null : $this->caCertificateDir)
            ->setUseNativeCaCertificates(true);
    }

    /**
     * @param non-empty-string|null $accessToken Токен доступа к API Max.
     * @return $this
     */
    public function setAccessToken(#[SensitiveParameter] ?string $accessToken): self
    {
        $this->accessToken = $accessToken !== null ? new SensitiveParameterValue($accessToken) : null;

        return $this;
    }

    /**
     * @param list<positive-int>|null $attachmentRetryAttempts Задержки перед повтором отправки
     *     при `attachment.not.ready`. `null` — использовать `$retryAttempts`, `[]` — не повторять отправку.
     * @return $this
     */
    public function setAttachmentRetryAttempts(?array $attachmentRetryAttempts): self
    {
        $this->attachmentRetryAttempts = $attachmentRetryAttempts;

        return $this;
    }

    /**
     * @param non-empty-string $baseUrl Базовый адрес API Max.
     * @return $this
     */
    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * @param non-empty-string|null $caCertificateDir Путь к каталогу с собственными корневыми сертификатами
     *     для проверки сервера (curl `CAPATH`). Игнорируется в {@see PsrHttpClient}.
     * @return $this
     */
    public function setCaCertificateDir(?string $caCertificateDir): self
    {
        $this->caCertificateDir = $caCertificateDir;

        return $this;
    }

    /**
     * @param non-empty-string|null $caCertificatePath Путь к собственному набору корневых сертификатов
     *     для проверки сервера (curl `CAINFO`). Игнорируется в {@see PsrHttpClient}.
     * @return $this
     */
    public function setCaCertificatePath(?string $caCertificatePath): self
    {
        $this->caCertificatePath = $caCertificatePath;

        return $this;
    }

    /**
     * @param non-negative-int $connectTimeout Сколько **миллисекунд** ждать подключения.
     *     `0` — ждать сколько угодно. Игнорируется в {@see PsrHttpClient}.
     * @return $this
     */
    public function setConnectTimeout(int $connectTimeout): self
    {
        $this->connectTimeout = $connectTimeout;

        return $this;
    }

    /**
     * @param HttpClientInterface|null $httpClient HTTP-клиент для запросов к API.
     * @return $this
     */
    public function setHttpClient(?HttpClientInterface $httpClient): self
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * @param HttpClientInterface|null $httpClientForUpdates HTTP-клиент для длинного опроса обновлений.
     * @return $this
     */
    public function setHttpClientForUpdates(?HttpClientInterface $httpClientForUpdates): self
    {
        $this->httpClientForUpdates = $httpClientForUpdates;

        return $this;
    }

    /**
     * @param HttpClientInterface|null $httpClientForUploader HTTP-клиент для загрузки медиафайлов.
     * @return $this
     */
    public function setHttpClientForUploader(?HttpClientInterface $httpClientForUploader): self
    {
        $this->httpClientForUploader = $httpClientForUploader;

        return $this;
    }

    /**
     * @param list<positive-int>|null $postRetryAttempts Задержки повторов POST в миллисекундах.
     *     `null` — использовать `$retryAttempts`, `[]` — отключить транспортные повторы POST.
     * @return $this
     */
    public function setPostRetryAttempts(?array $postRetryAttempts): self
    {
        $this->postRetryAttempts = $postRetryAttempts;

        return $this;
    }

    /**
     * @param list<positive-int> $retryAttempts Задержка перед повторной попыткой в миллисекундах.
     * @return $this
     */
    public function setRetryAttempts(array $retryAttempts): self
    {
        $this->retryAttempts = $retryAttempts;

        return $this;
    }

    /**
     * @param non-negative-int $timeout Сколько **миллисекунд** может выполняться запрос. `0` — без
     *     ограничения. Игнорируется в {@see PsrHttpClient}.
     * @return $this
     */
    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * @param bool $verifySslCertificate `false` отключает проверку TLS-сертификата сервера.
     *     Это небезопасно и годится только для локальных проверок. Игнорируется в {@see PsrHttpClient}.
     * @return $this
     */
    public function setVerifySslCertificate(bool $verifySslCertificate): self
    {
        $this->verifySslCertificate = $verifySslCertificate;

        return $this;
    }

    /**
     * Доверяет корневым сертификатам Минцифры, поставляемым с пакетом.
     *
     * Указывает {@see caCertificatePath} на набор сертификатов из `resources/certs`.
     * Нужен, когда API Max отдаёт сертификат, выданный центром Минцифры, которого нет в системном хранилище доверия.
     *
     * @return $this
     */
    public function useRussianTrustedCaCertificates(): self
    {
        $this->caCertificatePath = dirname(__DIR__) . '/resources/certs/russian_trusted_ca_bundle.pem';

        return $this;
    }
}
