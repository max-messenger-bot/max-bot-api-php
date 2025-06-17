<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Exceptions\Validation\MatchException;
use MaxMessenger\Bot\Models\Enums\UpdateType;

use function array_key_exists;

/**
 * Request to set up WebHook subscription.
 *
 * @api
 */
final class SubscriptionRequestBody extends BaseRequestModel
{
    use ValidateTrait;

    /**
     * @var array{
     *     secret?: non-empty-string,
     *     update_types?: list<UpdateType>,
     *     url: non-empty-string,
     *     version?: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @param non-empty-string $url URL of HTTP(S)-endpoint of your bot. Must starts with http(s)://.
     * @param non-empty-string|null $secret A secret to be sent in a header "X-Max-Bot-Api-Secret" in every webhook
     *     request, 5-256 characters. Only characters A-Z, a-z, 0-9, _ and - are allowed (minLength: 5, maxLength: 256).
     * @param array<UpdateType>|null $update_types List of update types your bot want to receive.
     *     See `Update` object for a complete list of types.
     * @param non-empty-string|null $version Version of API. Affects model representation.
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
     * @param non-empty-string|null $url URL of HTTP(S)-endpoint of your bot. Must starts with http(s)://.
     * @psalm-param non-empty-string $url
     * @param non-empty-string|null $secret A secret to be sent in a header "X-Max-Bot-Api-Secret" in every webhook
     *     request, 5-256 characters. Only characters A-Z, a-z, 0-9, _ and - are allowed (minLength: 5, maxLength: 256).
     * @param array<UpdateType>|null $update_types List of update types your bot want to receive.
     *     See `Update` object for a complete list of types.
     * @param non-empty-string|null $version Version of API. Affects model representation.
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
     * @param non-empty-string $secret A secret to be sent in a header "X-Max-Bot-Api-Secret" in every webhook request,
     *     5-256 characters. Only characters A-Z, a-z, 0-9, _ and - are allowed (minLength: 5, maxLength: 256).
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
     * @param array<UpdateType> $update_types List of update types your bot want to receive.
     *     See `Update` object for a complete list of types.
     * @return $this
     * @api
     */
    public function setUpdateTypes(array $update_types): static
    {
        $this->data['update_types'] = array_values(array_unique($update_types));

        return $this;
    }

    /**
     * @param non-empty-string $url URL of HTTP(S)-endpoint of your bot. Must starts with http(s)://.
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
     * @param non-empty-string $version Version of API. Affects model representation.
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
