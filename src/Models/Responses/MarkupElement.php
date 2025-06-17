<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

use MaxMessenger\Bot\Models\Enums\MarkupElementType;

/**
 * Тип элемента разметки.
 *
 * Может быть \*\*жирный\*\*, \*курсив\*, \~зачеркнутый\~, \<ins>подчеркнутый\</ins>, \`моноширинный\`,
 * ссылка или упоминание пользователя.
 */
class MarkupElement extends BaseResponseModel
{
    /**
     * @var array{
     *     type: non-empty-string,
     *     from: int,
     *     length: int
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return int Индекс начала элемента разметки в тексте. Нумерация с нуля.
     */
    public function getFrom(): int
    {
        return $this->data['from'];
    }

    /**
     * @return int Длина элемента разметки.
     */
    public function getLength(): int
    {
        return $this->data['length'];
    }

    /**
     * @return MarkupElementType|null Тип элемента разметки. Может быть \*\*жирный\*\*, \*курсив\*, \~зачеркнутый\~,
     *     \<ins>подчеркнутый\</ins>, \`моноширинный\`, ссылка или упоминание пользователя.
     */
    public function getType(): ?MarkupElementType
    {
        return MarkupElementType::tryFrom($this->data['type']);
    }

    /**
     * @return non-empty-string Тип элемента разметки. Может быть \*\*жирный\*\*, \*курсив\*, \~зачеркнутый\~,
     *     \<ins>подчеркнутый\</ins>, \`моноширинный\`, ссылка или упоминание пользователя.
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
            /** @psalm-var static Psalm bug */
            return parent::newFromData($data);
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
        $className = $classList[$data['type']] ?? null;

        /** @psalm-var static Psalm bug */
        return $className !== null
            ? $className::newFromData($data)
            : parent::newFromData($data);
    }
}
