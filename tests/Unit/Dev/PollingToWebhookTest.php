<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Dev;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Dev\PollingToWebhook;

/**
 * Сборка переменных CGI-окружения для скрипта-обработчика.
 */
final class PollingToWebhookTest extends Unit
{
    public function testDefaults(): void
    {
        $env = PollingToWebhook::makeEnv('/var/www/app/public/webhook.php', '/var/www/app/public', 's3cret', 17);

        self::assertSame('POST', $env['REQUEST_METHOD']);
        self::assertSame('/var/www/app/public', $env['DOCUMENT_ROOT']);
        self::assertSame('/var/www/app/public/webhook.php', $env['SCRIPT_FILENAME']);
        self::assertSame('/webhook.php', $env['SCRIPT_NAME']);
        self::assertSame('/webhook.php', $env['PHP_SELF']);
        self::assertSame('/webhook.php', $env['REQUEST_URI']);
        self::assertSame('localhost', $env['HTTP_HOST']);
        self::assertSame('localhost', $env['SERVER_NAME']);
        self::assertSame('80', $env['SERVER_PORT']);
        self::assertSame('http', $env['REQUEST_SCHEME']);
        self::assertSame('127.0.0.1', $env['REMOTE_ADDR']);
        self::assertSame('HTTP/1.1', $env['SERVER_PROTOCOL']);
        self::assertSame('CGI/1.1', $env['GATEWAY_INTERFACE']);
        self::assertSame('application/json', $env['HTTP_ACCEPT']);
        self::assertSame('application/json; charset=UTF-8', $env['CONTENT_TYPE']);
        self::assertSame('17', $env['CONTENT_LENGTH']);
        self::assertSame('s3cret', $env['HTTP_X_MAX_BOT_API_SECRET']);
        self::assertArrayNotHasKey('HTTPS', $env);
    }

    public function testHostWithPort(): void
    {
        $env = PollingToWebhook::makeEnv(
            '/var/www/app/public/index.php',
            '/var/www/app/public',
            's3cret',
            2,
            ['HTTP_HOST' => 'example.test:8080'],
        );

        self::assertSame('example.test:8080', $env['HTTP_HOST']);
        self::assertSame('example.test', $env['SERVER_NAME']);
        self::assertSame('8080', $env['SERVER_PORT']);
    }

    public function testHttps(): void
    {
        $env = PollingToWebhook::makeEnv(
            '/var/www/app/public/index.php',
            '/var/www/app/public',
            's3cret',
            2,
            ['SCHEME' => 'https', 'HTTP_HOST' => 'example.test'],
        );

        self::assertSame('on', $env['HTTPS']);
        self::assertSame('https', $env['REQUEST_SCHEME']);
        self::assertSame('443', $env['SERVER_PORT']);
    }

    public function testRemoteAddrFromConfig(): void
    {
        $env = PollingToWebhook::makeEnv(
            '/var/www/app/public/index.php',
            '/var/www/app/public',
            's3cret',
            2,
            ['REMOTE_ADDR' => '10.1.2.3'],
        );

        self::assertSame('10.1.2.3', $env['REMOTE_ADDR']);
    }

    public function testRequestUriFromConfig(): void
    {
        $env = PollingToWebhook::makeEnv(
            '/var/www/app/public/index.php',
            '/var/www/app/public',
            's3cret',
            2,
            ['REQUEST_URI' => '/api/max/webhook'],
        );

        self::assertSame('/index.php', $env['SCRIPT_NAME']);
        self::assertSame('/api/max/webhook', $env['REQUEST_URI']);
    }

    public function testScriptNameInSubfolder(): void
    {
        $env = PollingToWebhook::makeEnv('/var/www/app/public/max/hook.php', '/var/www/app/public/', 's3cret', 2);

        self::assertSame('/max/hook.php', $env['SCRIPT_NAME']);
        self::assertSame('/max/hook.php', $env['REQUEST_URI']);
    }
}
