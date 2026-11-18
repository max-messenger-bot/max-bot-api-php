<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Support\Fake;

use function strlen;
use function substr;

/**
 * Подменяет php://input в тесте обработки Webhook-запроса.
 *
 * Методы протокола потоков вызываются PHP автоматически.
 *
 * @api
 */
final class FakePhpInputStream
{
    /**
     * @var string Тело входящего запроса.
     */
    public static string $body = '';
    /**
     * @var resource|null Контекст потока, устанавливаемый PHP.
     */
    public $context;
    /**
     * @var int Текущая позиция чтения.
     */
    private int $offset = 0;

    /**
     * Проверяет конец потока при чтении PHP.
     */
    public function stream_eof(): bool
    {
        return $this->offset >= strlen(self::$body);
    }

    /**
     * Открывает подменённый входной поток по запросу PHP.
     *
     * @param string $path Адрес открываемого потока.
     * @param string $unusedMode Режим открытия из протокола потоков PHP.
     * @param int $unusedOptions Параметры открытия из протокола потоков PHP.
     * @param string|null $unusedOpenedPath Полный путь из протокола потоков PHP.
     */
    public function stream_open(string $path, string $unusedMode, int $unusedOptions, ?string &$unusedOpenedPath): bool
    {
        $this->offset = 0;

        return $path === 'php://input';
    }

    /**
     * Читает очередную часть входного запроса по запросу PHP.
     *
     * @param int $count Максимальное число байт для чтения.
     */
    public function stream_read(int $count): string
    {
        $chunk = substr(self::$body, $this->offset, $count);
        $this->offset += strlen($chunk);

        return $chunk;
    }

    /**
     * Возвращает пустые сведения о потоке по запросу PHP.
     *
     * @return array{}
     */
    public function stream_stat(): array
    {
        return [];
    }
}
