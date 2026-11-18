<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Contract;

use MaxMessenger\Bot\Contract\Http\HttpClientInterface;
use SensitiveParameterValue;

/**
 * Интерфейс хранилища настроек.
 *
 * Его можно реализовать самостоятельно.
 */
interface MaxApiConfigInterface
{
    /**
     * @return SensitiveParameterValue<non-empty-string>|null Токен доступа к API Max.
     */
    public function getAccessToken(): ?SensitiveParameterValue;

    /**
     * @return list<positive-int> Задержки перед повтором отправки при `attachment.not.ready`.
     */
    public function getAttachmentRetryAttempts(): array;

    /**
     * @return non-empty-string Базовый адрес API Max.
     */
    public function getBaseUrl(): string;

    /**
     * @return HttpClientInterface HTTP-клиент для запросов к API.
     */
    public function getHttpClient(): HttpClientInterface;

    /**
     * @return HttpClientInterface|null HTTP-клиент для длинного опроса обновлений.
     */
    public function getHttpClientForUpdates(): ?HttpClientInterface;

    /**
     * @return HttpClientInterface HTTP-клиент для загрузки медиафайлов.
     */
    public function getHttpClientForUploader(): HttpClientInterface;

    /**
     * @return list<positive-int> Задержки повторов POST в миллисекундах.
     */
    public function getPostRetryAttempts(): array;

    /**
     * @return list<positive-int> Задержка перед повторной попыткой в миллисекундах.
     */
    public function getRetryAttempts(): array;
}
