<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\UpdateType;

use function array_key_exists;

/**
 * Запрос на настройку подписки WebHook.
 */
final class SubscriptionRequestBody extends BaseRequestModel
{
    use ValidateTrait;
    use ValidateRequiredTrait;

    /**
     * @var array{
     *     url: non-empty-string,
     *     update_types?: list<UpdateType>,
     *     secret?: non-empty-string,
     *     version?: non-empty-string
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param non-empty-string|null $url URL HTTPS-endpoint вашего бота (minLength: 12). Должен начинаться с `https://`.
     * @param non-empty-string|null $secret Секрет, который должен быть отправлен в заголовке
     *     `X-Max-Bot-Api-Secret` в каждом запросе Webhook (minLength: 5, maxLength: 256).
     *     Разрешены только символы `A-Z`, `a-z`, `0-9`, `_` и `-`.
     * @param UpdateType[]|null $update_types Список типов обновлений, которые хочет получать ваш бот.
     *     Для полного списка типов см. объект `Update`.
     * @param non-empty-string|null $version Версия API (minLength: 1, pattern: `[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}`).
     *     Влияет на представление модели.
     */
    public function __construct(
        ?string $url = null,
        ?string $secret = null,
        ?array $update_types = null,
        ?string $version = null
    ) {
        $this->required = ['url'];

        if ($url !== null) {
            $this->setUrl($url);
        }
        if ($secret !== null) {
            $this->setSecret($secret);
        }
        if ($update_types !== null) {
            $this->setUpdateTypes($update_types);
        }
        if ($version !== null) {
            $this->setVersion($version);
        }
    }

    /**
     * @return non-empty-string|null
     */
    public function getSecret(): ?string
    {
        return $this->data['secret'] ?? null;
    }

    /**
     * @return list<UpdateType>|null
     */
    public function getUpdateTypes(): ?array
    {
        return $this->data['update_types'] ?? null;
    }

    /**
     * @return non-empty-string
     */
    public function getUrl(): string
    {
        return $this->data['url'];
    }

    /**
     * @return non-empty-string|null
     */
    public function getVersion(): ?string
    {
        return $this->data['version'] ?? null;
    }

    public function issetSecret(): bool
    {
        return array_key_exists('secret', $this->data);
    }

    public function issetUpdateTypes(): bool
    {
        return array_key_exists('update_types', $this->data);
    }

    public function issetUrl(): bool
    {
        return array_key_exists('url', $this->data);
    }

    public function issetVersion(): bool
    {
        return array_key_exists('version', $this->data);
    }

    /**
     * @param non-empty-string $url URL HTTPS-endpoint вашего бота (minLength: 12). Должен начинаться с `https://`.
     * @param non-empty-string|null $secret Секрет, который должен быть отправлен в заголовке
     *     `X-Max-Bot-Api-Secret` в каждом запросе Webhook (minLength: 5, maxLength: 256).
     *     Разрешены только символы `A-Z`, `a-z`, `0-9`, `_` и `-`.
     * @param UpdateType[]|null $update_types Список типов обновлений, которые хочет получать ваш бот.
     *     Для полного списка типов см. объект `Update`.
     * @param non-empty-string|null $version Версия API (minLength: 1, pattern: `[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}`).
     *     Влияет на представление модели.
     */
    public static function make(
        string $url,
        ?string $secret = null,
        ?array $update_types = null,
        ?string $version = null
    ): self {
        return new self($url, $secret, $update_types, $version);
    }

    /**
     * @param non-empty-string|null $url URL HTTPS-endpoint вашего бота (minLength: 12). Должен начинаться с `https://`.
     * @param non-empty-string|null $secret Секрет, который должен быть отправлен в заголовке
     *     `X-Max-Bot-Api-Secret` в каждом запросе Webhook (minLength: 5, maxLength: 256).
     *     Разрешены только символы `A-Z`, `a-z`, `0-9`, `_` и `-`.
     * @param UpdateType[]|null $update_types Список типов обновлений, которые хочет получать ваш бот.
     *     Для полного списка типов см. объект `Update`.
     * @param non-empty-string|null $version Версия API (minLength: 1, pattern: `[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}`).
     *     Влияет на представление модели.
     */
    public static function new(
        ?string $url = null,
        ?string $secret = null,
        ?array $update_types = null,
        ?string $version = null
    ): self {
        return new self($url, $secret, $update_types, $version);
    }

    /**
     * @param non-empty-string $secret Секрет, который должен быть отправлен в заголовке
     *     `X-Max-Bot-Api-Secret` в каждом запросе Webhook (minLength: 5, maxLength: 256).
     *     Разрешены только символы `A-Z`, `a-z`, `0-9`, `_` и `-`.
     * @return $this
     */
    public function setSecret(string $secret): self
    {
        self::validateString('secret', $secret, minLength: 5, maxLength: 256, pattern: '/^[a-zA-Z0-9_-]{5,256}$/');

        $this->data['secret'] = $secret;

        return $this;
    }

    /**
     * @param UpdateType[] $update_types Список типов обновлений, которые хочет получать ваш бот.
     *     Для полного списка типов см. объект `Update`.
     * @return $this
     */
    public function setUpdateTypes(array $update_types): self
    {
        $this->data['update_types'] = array_values($update_types);

        return $this;
    }

    /**
     * @param non-empty-string $url URL HTTPS-endpoint вашего бота (minLength: 12). Должен начинаться с `https://`.
     * @return $this
     */
    public function setUrl(string $url): self
    {
        self::validateString('url', $url, minLength: 12);

        $this->data['url'] = $url;

        return $this;
    }

    /**
     * @param non-empty-string $version Версия API (minLength: 1, pattern: `[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}`).
     *     Влияет на представление модели.
     * @return $this
     */
    public function setVersion(string $version): self
    {
        self::validateString('version', $version, minLength: 1, pattern: '/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/');

        $this->data['version'] = $version;

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetSecret(): self
    {
        unset($this->data['secret']);

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetUpdateTypes(): self
    {
        unset($this->data['update_types']);

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetVersion(): self
    {
        unset($this->data['version']);

        return $this;
    }
}
