<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Exceptions\Validation\MatchException;
use MaxMessenger\Bot\Models\Enums\UpdateType;

use function array_key_exists;

/**
 * Запрос на настройку подписки WebHook.
 *
 * @api
 */
final class SubscriptionRequestBody extends BaseRequestModel
{
    use ValidateTrait;

    /**
     * @var array{
     *     url: non-empty-string,
     *     update_types?: list<UpdateType>,
     *     secret?: non-empty-string,
     *     version?: non-empty-string
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType
     */
    protected array $data = [];

    /**
     * @param non-empty-string $url URL HTTPS-endpoint вашего бота. Должен начинаться с `https://`.
     * @param non-empty-string|null $secret Секрет, который должен быть отправлен в заголовке
     *     `X-Max-Bot-Api-Secret` в каждом запросе Webhook. Разрешены только символы `A-Z`, `a-z`, `0-9`, `_` и `-`
     *     (minLength: 5, maxLength: 256).
     * @param UpdateType[]|null $update_types Список типов обновлений, которые хочет получать ваш бот.
     *     Для полного списка типов см. объект `Update`.
     * @param non-empty-string|null $version Версия API. Влияет на представление модели.
     * @api
     */
    public function __construct(
        string $url,
        ?string $secret = null,
        ?array $update_types = null,
        ?string $version = null
    ) {
        $this->setUrl($url);
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
     * @api
     */
    public function getSecret(): ?string
    {
        return $this->data['secret'] ?? null;
    }

    /**
     * @return list<UpdateType>|null
     * @api
     */
    public function getUpdateTypes(): ?array
    {
        return $this->data['update_types'] ?? null;
    }

    /**
     * @return non-empty-string
     * @api
     */
    public function getUrl(): string
    {
        return $this->data['url'];
    }

    /**
     * @return non-empty-string|null
     * @api
     */
    public function getVersion(): ?string
    {
        return $this->data['version'] ?? null;
    }

    /**
     * @api
     */
    public function issetSecret(): bool
    {
        return array_key_exists('secret', $this->data);
    }

    /**
     * @api
     */
    public function issetUpdateTypes(): bool
    {
        return array_key_exists('update_types', $this->data);
    }

    /**
     * @api
     */
    public function issetVersion(): bool
    {
        return array_key_exists('version', $this->data);
    }

    /**
     * @param non-empty-string|null $url URL HTTPS-endpoint вашего бота. Должен начинаться с `https://`.
     * @psalm-param non-empty-string $url
     * @param non-empty-string|null $secret Секрет, который должен быть отправлен в заголовке
     *     `X-Max-Bot-Api-Secret` в каждом запросе Webhook. Разрешены только символы `A-Z`, `a-z`, `0-9`, `_` и `-`
     *     (minLength: 5, maxLength: 256).
     * @param UpdateType[]|null $update_types Список типов обновлений, которые хочет получать ваш бот.
     *     Для полного списка типов см. объект `Update`.
     * @param non-empty-string|null $version Версия API. Влияет на представление модели.
     * @noinspection PhpMissingParentCallCommonInspection
     * @api
     */
    public static function new(
        ?string $url = null,
        ?string $secret = null,
        ?array $update_types = null,
        ?string $version = null
    ): static {
        static::validateNotNull('url', $url);

        return new static($url, $secret, $update_types, $version);
    }

    /**
     * @param non-empty-string $secret Секрет, который должен быть отправлен в заголовке
     *     `X-Max-Bot-Api-Secret` в каждом запросе Webhook. Разрешены только символы `A-Z`, `a-z`, `0-9`, `_` и `-`
     *     (minLength: 5, maxLength: 256).
     * @return $this
     * @api
     */
    public function setSecret(string $secret): static
    {
        static::validateString('secret', $secret, minLength: 5, maxLength: 256);
        if (!preg_match('/^[a-zA-Z0-9_-]{5,256}$/', $secret)) {
            throw new MatchException('secret');
        }

        $this->data['secret'] = $secret;

        return $this;
    }

    /**
     * @param UpdateType[] $update_types Список типов обновлений, которые хочет получать ваш бот.
     *     Для полного списка типов см. объект `Update`.
     * @return $this
     * @api
     */
    public function setUpdateTypes(array $update_types): static
    {
        $this->data['update_types'] = array_values($update_types);

        return $this;
    }

    /**
     * @param non-empty-string $url URL HTTPS-endpoint вашего бота. Должен начинаться с `https://`.
     * @return $this
     * @api
     */
    public function setUrl(string $url): static
    {
        static::validateString('url', $url, minLength: 10);

        $this->data['url'] = $url;

        return $this;
    }

    /**
     * @param non-empty-string $version Версия API. Влияет на представление модели.
     * @return $this
     * @api
     */
    public function setVersion(string $version): static
    {
        static::validateString('version', $version, minLength: 1);

        $this->data['version'] = $version;

        return $this;
    }

    /**
     * @return $this
     * @api
     */
    public function unsetSecret(): static
    {
        unset($this->data['secret']);

        return $this;
    }

    /**
     * @return $this
     * @api
     */
    public function unsetUpdateTypes(): static
    {
        unset($this->data['update_types']);

        return $this;
    }

    /**
     * @return $this
     * @api
     */
    public function unsetVersion(): static
    {
        unset($this->data['version']);

        return $this;
    }
}
