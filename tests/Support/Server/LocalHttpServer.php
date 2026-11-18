<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Support\Server;

use RuntimeException;

use function fclose;
use function fsockopen;
use function getenv;
use function is_resource;
use function microtime;
use function proc_get_status;
use function proc_open;
use function proc_terminate;
use function stream_socket_get_name;
use function stream_socket_server;
use function strrpos;
use function substr;
use function usleep;

use const DIRECTORY_SEPARATOR;
use const PHP_BINARY;
use const PHP_OS_FAMILY;

/**
 * Встроенный веб-сервер PHP на свободном порту `127.0.0.1` с маршрутизатором `router.php`.
 */
final class LocalHttpServer
{
    /**
     * @var resource|null Процесс сервера.
     */
    private $process = null;

    /**
     * @param non-empty-string $baseUrl Адрес сервера без завершающего слеша.
     * @param resource $process Процесс сервера.
     */
    private function __construct(
        public readonly string $baseUrl,
        $process,
    ) {
        $this->process = $process;
    }

    /**
     * Запускает сервер и ждёт, пока он начнёт принимать соединения.
     *
     * @throws RuntimeException Если сервер не удалось запустить.
     */
    public static function start(): self
    {
        $port = self::findFreePort();
        $null = PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';
        $env = getenv();

        if (PHP_OS_FAMILY === 'Linux') {
            $env['PHP_CLI_SERVER_WORKERS'] = '4';
        }

        $process = proc_open(
            [PHP_BINARY, '-S', '127.0.0.1:' . $port, __DIR__ . DIRECTORY_SEPARATOR . 'router.php'],
            [['file', $null, 'r'], ['file', $null, 'w'], ['file', $null, 'w']],
            $pipes,
            null,
            $env,
        );

        if (!is_resource($process)) {
            throw new RuntimeException('Failed to start the built-in PHP server.');
        }

        $server = new self('http://127.0.0.1:' . $port, $process);
        $server->waitUntilReady($port);

        return $server;
    }

    /**
     * Останавливает сервер.
     */
    public function stop(): void
    {
        if ($this->process !== null) {
            proc_terminate($this->process);
            $this->process = null;
        }
    }

    /**
     * @return positive-int Свободный порт на `127.0.0.1`.
     */
    private static function findFreePort(): int
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0');

        if ($socket === false) {
            throw new RuntimeException('Failed to find a free port.');
        }

        $name = (string) stream_socket_get_name($socket, false);
        fclose($socket);
        $port = (int) substr($name, (int) strrpos($name, ':') + 1);

        if ($port <= 0) {
            throw new RuntimeException('Failed to find a free port.');
        }

        return $port;
    }

    private function waitUntilReady(int $port): void
    {
        $deadline = microtime(true) + 10.0;

        while (microtime(true) < $deadline) {
            $connection = @fsockopen('127.0.0.1', $port, $errorCode, $errorMessage, 0.1);

            if (is_resource($connection)) {
                fclose($connection);

                return;
            }

            if ($this->process === null || !proc_get_status($this->process)['running']) {
                break;
            }

            usleep(50_000);
        }

        $this->stop();

        throw new RuntimeException('The built-in PHP server did not start.');
    }
}
