<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

use MaxMessenger\Bot\Model\Enum\MarkupElementType;

/**
 * Тип элемента разметки.
 *
 * Может быть \*\*жирный\*\*, \*курсив\*, ~~зачёркнутый~~, ++подчёркнутый++, \`моноширинный\`, ^^выделенный^^,
 * цитата, заголовок, ссылка или упоминание пользователя.
 *
 * @link https://dev.max.ru/docs-api/objects/Message/
 * @link https://dev.max.ru/docs-api/use-cases/sending-messages/text-formatting
 */
class MarkupElement extends BaseResponseModel
{
    /**
     * @var array{
     *     type: non-empty-string,
     *     from: non-negative-int,
     *     length: non-negative-int
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return non-negative-int Индекс начала элемента разметки в тексте. Нумерация с нуля.
     */
    public function getFrom(): int
    {
        return $this->data['from'];
    }

    /**
     * @return non-negative-int Длина элемента разметки в символах.
     */
    public function getLength(): int
    {
        return $this->data['length'];
    }

    /**
     * @return MarkupElementType|null Тип элемента разметки.
     */
    public function getType(): ?MarkupElementType
    {
        return MarkupElementType::tryFrom($this->data['type']);
    }

    /**
     * @return non-empty-string Тип элемента разметки. Значения описаны в {@see MarkupElementType}.
     */
    public function getTypeRaw(): string
    {
        return $this->data['type'];
    }

    /**
     * Создаёт элемент разметки нужного типа по данным ответа API.
     *
     * @param array $data Данные элемента разметки из ответа API.
     */
    public static function newFromData(array $data): static
    {
        if (static::class !== self::class) {
            return parent::newFromData($data);
        }

        $classList = [
            'emphasized' => EmphasizedMarkup::class,
            'heading' => HeadingMarkup::class,
            'highlighted' => HighlightedMarkup::class,
            'link' => LinkMarkup::class,
            'monospaced' => MonospacedMarkup::class,
            'quote' => QuoteMarkup::class,
            'strikethrough' => StrikethroughMarkup::class,
            'strong' => StrongMarkup::class,
            'underline' => UnderlineMarkup::class,
            'user_mention' => UserMentionMarkup::class,
        ];
        /** @var array{type: string} $data */
        $className = $classList[$data['type']] ?? null;

        return $className !== null
            ? $className::newFromData($data)
            : parent::newFromData($data);
    }
}
