<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Enum\MarkupElementType;
use MaxMessenger\Bot\Model\Response\EmphasizedMarkup;
use MaxMessenger\Bot\Model\Response\HeadingMarkup;
use MaxMessenger\Bot\Model\Response\HighlightedMarkup;
use MaxMessenger\Bot\Model\Response\LinkMarkup;
use MaxMessenger\Bot\Model\Response\MarkupElement;
use MaxMessenger\Bot\Model\Response\MonospacedMarkup;
use MaxMessenger\Bot\Model\Response\QuoteMarkup;
use MaxMessenger\Bot\Model\Response\StrikethroughMarkup;
use MaxMessenger\Bot\Model\Response\StrongMarkup;
use MaxMessenger\Bot\Model\Response\UnderlineMarkup;
use MaxMessenger\Bot\Model\Response\UserMentionMarkup;

/**
 * Создание элементов разметки по типу и общие методы {@see MarkupElement}.
 */
final class MarkupElementTest extends Unit
{
    /**
     * @return iterable<string, array{array<string, mixed>, class-string<MarkupElement>, MarkupElementType}>
     */
    public static function markupTypes(): iterable
    {
        yield 'emphasized' => [['type' => 'emphasized'], EmphasizedMarkup::class, MarkupElementType::Emphasized];
        yield 'heading' => [['type' => 'heading', 'level' => 1], HeadingMarkup::class, MarkupElementType::Heading];
        yield 'highlighted' => [['type' => 'highlighted'], HighlightedMarkup::class, MarkupElementType::Highlighted];
        yield 'link' => [
            ['type' => 'link', 'url' => 'https://example.test'],
            LinkMarkup::class,
            MarkupElementType::Link,
        ];
        yield 'monospaced' => [['type' => 'monospaced'], MonospacedMarkup::class, MarkupElementType::Monospaced];
        yield 'quote' => [['type' => 'quote'], QuoteMarkup::class, MarkupElementType::Quote];
        yield 'strikethrough' => [
            ['type' => 'strikethrough'],
            StrikethroughMarkup::class,
            MarkupElementType::Strikethrough,
        ];
        yield 'strong' => [['type' => 'strong'], StrongMarkup::class, MarkupElementType::Strong];
        yield 'underline' => [['type' => 'underline'], UnderlineMarkup::class, MarkupElementType::Underline];
        yield 'user_mention' => [
            ['type' => 'user_mention', 'user_id' => 200],
            UserMentionMarkup::class,
            MarkupElementType::UserMention,
        ];
    }

    public function testHeadingLevels(): void
    {
        foreach ([1, 2, 3, 4] as $level) {
            $data = ['type' => 'heading', 'from' => 3, 'length' => 7, 'level' => $level];
            $element = MarkupElement::newFromData($data);

            self::assertInstanceOf(HeadingMarkup::class, $element);
            self::assertSame($level, $element->getLevel());
            self::assertSame(3, $element->getFrom());
            self::assertSame(7, $element->getLength());
            self::assertSame($data, $element->getRawData());
        }
    }

    public function testListFromData(): void
    {
        $list = MarkupElement::newListFromData([
            ['type' => 'strong', 'from' => 0, 'length' => 5],
            ['type' => 'quote', 'from' => 6, 'length' => 10],
        ]);

        self::assertCount(2, $list);
        self::assertInstanceOf(StrongMarkup::class, $list[0]);
        self::assertInstanceOf(QuoteMarkup::class, $list[1]);
        self::assertSame(6, $list[1]->getFrom());
    }

    /**
     * @param array<string, mixed> $data
     * @param class-string<MarkupElement> $class
     * @dataProvider markupTypes
     */
    public function testNewFromDataCreatesModelByType(array $data, string $class, MarkupElementType $type): void
    {
        $element = MarkupElement::newFromData($data + ['from' => 3, 'length' => 7]);

        self::assertInstanceOf($class, $element);
        self::assertSame($type, $element->getType());
        self::assertSame($type->value, $element->getTypeRaw());
        self::assertSame(3, $element->getFrom());
        self::assertSame(7, $element->getLength());
    }

    /**
     * @param array<string, mixed> $data
     * @param class-string<MarkupElement> $class
     * @dataProvider markupTypes
     */
    public function testSubclassNewFromDataCreatesItself(array $data, string $class, MarkupElementType $type): void
    {
        $element = $class::newFromData($data + ['from' => 0, 'length' => 1]);

        self::assertInstanceOf($class, $element);
        self::assertSame($type, $element->getType());
    }

    public function testUnknownType(): void
    {
        $element = MarkupElement::newFromData(['type' => 'unknown_markup', 'from' => 1, 'length' => 2]);

        self::assertSame(MarkupElement::class, $element::class);
        self::assertNull($element->getType());
        self::assertSame('unknown_markup', $element->getTypeRaw());
        self::assertSame(1, $element->getFrom());
        self::assertSame(2, $element->getLength());
    }
}
