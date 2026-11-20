<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\RequiredArgumentException;
use MaxMessenger\Bot\Model\Request\NewMessageBody;
use MaxMessenger\Bot\Model\Request\NewMessageLink;
use MaxMessenger\Bot\Model\Request\RawModel;

final class RawModelTest extends Unit
{
    public function testConstructor(): void
    {
        $data = [
            'text' => 'Привет, мир!',
            'text_format' => 'markdown',
            'notify' => true,
        ];

        $model = new RawModel($data);

        self::assertSame('Привет, мир!', $model['text']);
        self::assertSame('markdown', $model['text_format']);
        self::assertTrue($model['notify']);
    }

    public function testCustomModelExtendingRawModel(): void
    {
        $message = new class ('Привет, мир!') extends RawModel {
            public function __construct(string $text)
            {
                parent::__construct([
                    'text' => $text,
                    'text_format' => 'markdown',
                ]);
            }

            public function setReplyMessageId(string $messageId): self
            {
                $this['reply_to'] = ['message_id' => $messageId];

                return $this;
            }
        };

        $message->setReplyMessageId('mid.abc123');

        self::assertSame('Привет, мир!', $message['text']);
        self::assertSame('markdown', $message['text_format']);
        self::assertSame(['message_id' => 'mid.abc123'], $message['reply_to']);
    }

    public function testGetRawModelReturnsSelf(): void
    {
        $model = new RawModel(['text' => 'Test']);

        self::assertSame($model, $model->getRawModel());
    }

    public function testIsset(): void
    {
        $model = new RawModel([
            'text' => 'Test',
        ]);

        self::assertTrue(isset($model['text']));
        self::assertFalse(isset($model['non_existent']));
    }

    public function testNestedArraysConvertModelsToRawModel(): void
    {
        $inner = new RawModel(['mid' => 'mid.raw']);
        $model = new RawModel([
            'attachments' => [
                ['payload' => new NewMessageLink('mid.1'), 'raw' => $inner, 'text' => 'текст'],
                [],
            ],
            'empty' => [],
            'link' => new NewMessageLink('mid.2'),
            'raw' => $inner,
        ]);

        $attachments = $model['attachments'];
        self::assertIsArray($attachments);
        self::assertIsArray($attachments[0]);
        self::assertInstanceOf(RawModel::class, $attachments[0]['payload']);
        self::assertSame('mid.1', $attachments[0]['payload']['mid']);
        self::assertSame($inner, $attachments[0]['raw']);
        self::assertSame('текст', $attachments[0]['text']);
        self::assertSame([], $attachments[1]);
        self::assertSame([], $model['empty']);
        self::assertInstanceOf(RawModel::class, $model['link']);
        self::assertSame($inner, $model['raw']);
    }

    public function testNestedModelsConvertToRawModel(): void
    {
        $message = (new NewMessageBody('Текст сообщения'))
            ->setReplyLink('mid.ffffbea82cf265aa15ab6843019d844d');

        $rawMessage = $message->getRawModel();

        self::assertInstanceOf(RawModel::class, $rawMessage);
        self::assertSame('Текст сообщения', $rawMessage['text']);

        $link = $rawMessage['link'];
        self::assertInstanceOf(RawModel::class, $link);
        self::assertSame('mid.ffffbea82cf265aa15ab6843019d844d', $link['mid']);
    }

    public function testNew(): void
    {
        $data = [
            'url' => 'https://example.com',
            'secret' => 'test-secret',
        ];

        $model = RawModel::new($data);

        self::assertInstanceOf(RawModel::class, $model);
        self::assertSame('https://example.com', $model['url']);
        self::assertSame('test-secret', $model['secret']);
    }

    public function testNewEmpty(): void
    {
        $model = RawModel::new();

        self::assertInstanceOf(RawModel::class, $model);
        self::assertNull($model['text']);
        self::assertSame([], $model->getRawData());
    }

    public function testOffsetGetNonExistent(): void
    {
        $model = new RawModel([]);

        self::assertNull($model['non_existent']);
    }

    public function testOffsetSet(): void
    {
        $model = new RawModel([]);

        $model['text'] = 'Новый текст';
        $model['disable_notification'] = true;

        self::assertSame('Новый текст', $model['text']);
        self::assertTrue($model['disable_notification']);
    }

    public function testOffsetSetRejectsAppend(): void
    {
        $data = ['' => 'existing'];
        $model = new RawModel($data);

        $this->expectException(RequiredArgumentException::class);
        $this->expectExceptionMessage('The $offset argument cannot be null.');

        try {
            $model[] = 'new';
        } finally {
            self::assertSame($data, $model->getRawData());
        }
    }

    public function testOffsetUnset(): void
    {
        $model = new RawModel([
            'text' => 'Test',
            'text_format' => 'markdown',
        ]);

        unset($model['text_format']);

        self::assertSame('Test', $model['text']);
        self::assertNull($model['text_format']);
    }

    public function testSetRawData(): void
    {
        $model = new RawModel(['old_key' => 'old_value']);

        $newData = [
            'text' => 'Новый текст',
            'text_format' => 'markdown',
        ];

        $result = $model->setRawData($newData);

        self::assertSame($model, $result);
        self::assertSame('Новый текст', $model['text']);
        self::assertNull($model['old_key']);
    }
}
