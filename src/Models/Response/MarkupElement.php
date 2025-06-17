<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

use MaxMessenger\Bot\Models\Enums\MarkupElementType;

/**
 * Markup element for text formatting.
 *
 * @api
 */
readonly class MarkupElement extends BaseResponseModel
{
    /**
     * @var array{
     *     type: string,
     *     from: int,
     *     length: int
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @return int Element start index (zero-based) in text.
     * @api
     */
    public function getFrom(): int
    {
        return $this->data['from'];
    }

    /**
     * @return int Length of the markup element.
     * @api
     */
    public function getLength(): int
    {
        return $this->data['length'];
    }

    /**
     * @return MarkupElementType Type of the markup element. Can be \*\*strong\*\*, \*emphasized\*,
     *     \~strikethrough\~, \+\+underline\+\+, \`monospaced\`, link or user_mention.
     * @api
     */
    public function getType(): MarkupElementType
    {
        return MarkupElementType::from($this->data['type']);
    }

    /**
     * @return string Type of the markup element. Can be \*\*strong\*\*, \*emphasized\*,
     *     \~strikethrough\~, \+\+underline\+\+, \`monospaced\`, link or user_mention.
     * @api
     */
    public function getTypeRaw(): string
    {
        return $this->data['type'];
    }

    /**
     * Creates an object using a map.
     */
    public static function newFromData(array $data): static
    {
        if (static::class !== self::class) {
            parent::newFromData($data);
        }

        /** @var array<string, class-string<self>> $classList */
        $classList = [
            'emphasized' => EmphasizedMarkup::class,
            'heading' => HeadingMarkup::class,
            'highlighted' => HighlightedMarkup::class,
            'link' => LinkMarkup::class,
            'monospaced' => MonospacedMarkup::class,
            'strikethrough' => StrikethroughMarkup::class,
            'strong' => StrongMarkup::class,
            'underline' => UnderlineMarkup::class,
            'user_mention' => UserMentionMarkup::class,
        ];
        /** @var array{type: string} $data */
        $className = $classList[$data['type']] ?? self::class;

        return $className::newFromData($data);
    }
}
