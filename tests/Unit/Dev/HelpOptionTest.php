<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Dev;

use Codeception\Test\Unit;

use function dirname;
use function fclose;
use function is_resource;
use function proc_close;
use function proc_open;
use function stream_get_contents;

use const PHP_BINARY;

/**
 * Справка консольных утилит по ключам `-h` и `--help`.
 */
final class HelpOptionTest extends Unit
{
    /**
     * @param non-empty-string $tool
     * @param non-empty-string $option
     * @dataProvider tools
     */
    public function testPrintsHelpAndExits(string $tool, string $option): void
    {
        $script = dirname(__DIR__, 3) . '/bin/' . $tool . '.php';
        $process = proc_open(
            [PHP_BINARY, $script, $option],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
        );
        self::assertTrue(is_resource($process));

        fclose($pipes[0]);
        $stdout = (string) stream_get_contents($pipes[1]);
        $stderr = (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        self::assertSame(0, $exitCode, $stderr);
        self::assertStringStartsWith('Использование: ' . $tool . ' [-h|--help]', $stdout);
        self::assertStringContainsString('.max-tokens', $stdout);
    }

    /**
     * @return iterable<string, array{non-empty-string, non-empty-string}> Утилита и ключ справки.
     */
    public static function tools(): iterable
    {
        foreach (['max-debug', 'max-subscribe', 'max-subscribes', 'max-unsubscribe', 'polling-to-webhook'] as $tool) {
            yield $tool . ' --help' => [$tool, '--help'];
            yield $tool . ' -h' => [$tool, '-h'];
        }
    }
}
