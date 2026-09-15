<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Dev;

use MaxMessenger\Bot\Exception\SimpleQueryError;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Model\Response\Update;
use Throwable;

use function ctype_digit;
use function date;
use function dirname;
use function fclose;
use function fwrite;
use function is_dir;
use function is_resource;
use function json_encode;
use function ltrim;
use function proc_close;
use function proc_open;
use function rtrim;
use function sleep;
use function sprintf;
use function str_replace;
use function str_starts_with;
use function strlen;
use function strrpos;
use function substr;

/**
 * Long Polling → Webhook Bridge
 *
 * Получает события через Long Polling и передаёт их скрипту-обработчику
 * через php-cgi, эмулируя входящий Webhook-запрос.
 */
final class PollingToWebhook
{
    private const CONFIG_NAME = '.polling-to-webhook.conf';

    public static function main(): void
    {
        $candidates = self::configCandidates();
        [$configPath, $config] = Utils::loadConfigFromFiles($candidates);

        if ($configPath === null) {
            self::printConfigNotFound($candidates);
            exit(1);
        }

        $script = $config['SCRIPT_FILENAME'] ?? null;
        $bin = $config['BIN'] ?? null;
        $secret = $config['MAXBOT_SECRET'] ?? null;
        $documentRoot = $config['DOCUMENT_ROOT'] ?? null;

        if ($script === null) {
            fwrite(STDERR, sprintf("❌ Параметр SCRIPT_FILENAME не задан в %s\n", $configPath));
            exit(1);
        }

        if ($bin === null) {
            fwrite(STDERR, sprintf("❌ Параметр BIN не задан в %s\n", $configPath));
            exit(1);
        }

        if ($secret === null) {
            fwrite(STDERR, sprintf("❌ Параметр MAXBOT_SECRET не задан в %s\n", $configPath));
            exit(1);
        }

        if ($documentRoot === null) {
            fwrite(STDERR, sprintf("❌ Параметр DOCUMENT_ROOT не задан в %s\n", $configPath));
            exit(1);
        }

        if (!is_dir($documentRoot)) {
            fwrite(STDERR, sprintf("❌ Папка DOCUMENT_ROOT не найдена: %s\n", $documentRoot));
            exit(1);
        }

        $scriptName = self::makeScriptName($script, $documentRoot);

        if (!str_starts_with(
            str_replace('\\', '/', $script),
            rtrim(str_replace('\\', '/', $documentRoot), '/') . '/',
        )) {
            fwrite(
                STDERR,
                sprintf(
                    "❌ Скрипт-обработчик должен находиться внутри DOCUMENT_ROOT:\n   SCRIPT_FILENAME: %s\n   DOCUMENT_ROOT  : %s\n",
                    $script,
                    $documentRoot,
                ),
            );
            exit(1);
        }

        $accessToken = $config['MAXBOT_ACCESS_TOKEN'] ?? Utils::requestAccessToken();

        Utils::printHeader('Polling → Webhook Bridge');
        echo sprintf("Config      : %s\n", $configPath);
        echo sprintf("Script      : %s\n", $script);
        echo sprintf("Document root: %s\n", $documentRoot);
        echo sprintf("Request URI : %s\n", $config['REQUEST_URI'] ?? $scriptName);
        echo sprintf("BIN         : %s\n", $bin);
        echo "\nНажмите Ctrl+C для остановки\n";
        Utils::printLine(true);

        $client = new MaxApiClient($accessToken);
        $marker = null;

        while (true) {
            try {
                echo sprintf("[%s] Ожидание событий...\n", date('Y-m-d H:i:s'));

                $response = $client->getUpdates(limit: 1, marker: $marker);
                $updates = $response->getUpdates();
                $marker = $response->getMarker();

                if (empty($updates)) {
                    continue;
                }

                foreach ($updates as $update) {
                    self::dispatchUpdate($update, $script, $bin, $secret, $documentRoot, $config);
                }
            } catch (SimpleQueryError $e) {
                fwrite(STDERR, sprintf("❌ Ошибка API: %s\n\n", $e->getMessage()));
                sleep(5);
            } catch (Throwable $e) {
                fwrite(STDERR, sprintf("❌ Ошибка: %s\n\n", $e->getMessage()));
                sleep(5);
            }
        }
    }

    /**
     * Собирает переменные CGI-окружения запроса.
     *
     * Окружение достаточно для фреймворков, создающих объект запроса из глобального контекста
     * (например `Illuminate\Http\Request::capture()` или
     * `Symfony\Component\HttpFoundation\Request::createFromGlobals()`).
     *
     * @param string $script Полный путь к скрипту-обработчику.
     * @param string $documentRoot Корневая папка сайта, в которой лежит скрипт-обработчик.
     * @param array<string, string> $config Конфигурация: `REQUEST_URI`, `HTTP_HOST`, `SCHEME`, `REMOTE_ADDR`.
     * @return array<string, string> Переменные окружения для php-cgi.
     * @internal
     */
    public static function makeEnv(
        string $script,
        string $documentRoot,
        string $secret,
        int $contentLength,
        array $config = [],
    ): array {
        $scriptName = self::makeScriptName($script, $documentRoot);
        $isHttps = ($config['SCHEME'] ?? 'http') === 'https';
        $host = $config['HTTP_HOST'] ?? 'localhost';
        [$serverName, $port] = self::splitHost($host, $isHttps);

        $env = [
            'REDIRECT_STATUS' => '200',
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => $config['REQUEST_URI'] ?? $scriptName,
            'DOCUMENT_ROOT' => $documentRoot,
            'SCRIPT_FILENAME' => $script,
            'SCRIPT_NAME' => $scriptName,
            'PHP_SELF' => $scriptName,
            'QUERY_STRING' => '',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'SERVER_SOFTWARE' => 'PollingToWebhook',
            'GATEWAY_INTERFACE' => 'CGI/1.1',
            'SERVER_NAME' => $serverName,
            'SERVER_PORT' => $port,
            'REQUEST_SCHEME' => $isHttps ? 'https' : 'http',
            'REMOTE_ADDR' => $config['REMOTE_ADDR'] ?? '127.0.0.1',
            'CONTENT_TYPE' => 'application/json; charset=UTF-8',
            'CONTENT_LENGTH' => (string) $contentLength,
            'HTTP_HOST' => $host,
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_CONNECTION' => 'close',
            'HTTP_X_MAX_BOT_API_SECRET' => $secret,
            'HTTP_USER_AGENT' => 'OneMe/0.1.10 Bot API',
        ];

        // Защищённость соединения фреймворки определяют по HTTPS, а не по REQUEST_SCHEME
        if ($isHttps) {
            $env['HTTPS'] = 'on';
        }

        return $env;
    }

    /**
     * Пути-кандидаты для поиска конфига: корень проекта и его папка dev/.
     *
     * @return list<string>
     */
    private static function configCandidates(): array
    {
        return Utils::projectFileCandidates(self::CONFIG_NAME);
    }

    /**
     * @param array<string, string> $config
     */
    private static function dispatchUpdate(
        Update $update,
        string $script,
        string $bin,
        string $secret,
        string $documentRoot,
        array $config,
    ): void {
        $body = json_encode($update->getRawData(), JSON_UNESCAPED_UNICODE);

        if ($body === false) {
            fwrite(STDERR, "❌ Ошибка сериализации события\n");

            return;
        }

        $env = self::makeEnv($script, $documentRoot, $secret, strlen($body), $config);

        Utils::printLine();
        echo sprintf("Событие: %s\n", $update->getUpdateTypeRaw());
        Utils::printLine();

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => STDOUT,
            2 => STDERR,
        ];

        // -d html_errors=0 — отключаем HTML-обёртку ошибок php-cgi (иначе сообщения приходят в тегах)
        $process = proc_open([$bin, '-d', 'html_errors=0', $script], $descriptors, $pipes, null, $env); // nosemgrep

        if (!is_resource($process)) {
            fwrite(STDERR, "❌ Не удалось запустить скрипт\n");

            return;
        }

        fwrite($pipes[0], $body);
        fclose($pipes[0]);
        proc_close($process);
    }

    /**
     * Путь скрипта-обработчика относительно корневой папки сайта.
     *
     * @return non-empty-string
     */
    private static function makeScriptName(string $script, string $documentRoot): string
    {
        $documentRoot = rtrim(str_replace('\\', '/', $documentRoot), '/');
        $script = str_replace('\\', '/', $script);

        return '/' . ltrim(substr($script, strlen($documentRoot)), '/');
    }

    /**
     * @param list<string> $candidates
     */
    private static function printConfigNotFound(array $candidates): void
    {
        $example = dirname(__DIR__, 2) . '/dev/' . self::CONFIG_NAME . '.example';
        $projectRoot = Utils::projectRoot();

        fwrite(STDERR, "❌ Конфиг polling-to-webhook не найден.\n\n");
        fwrite(STDERR, "Искал в:\n");
        foreach ($candidates as $candidate) {
            fwrite(STDERR, sprintf("  • %s\n", $candidate));
        }
        fwrite(STDERR, "\nСкопируйте файл-пример в корень проекта или в папку dev/ и заполните параметры:\n\n");
        fwrite(STDERR, sprintf("  cp %s %s\n", $example, $projectRoot . '/' . self::CONFIG_NAME));
        fwrite(STDERR, "  # или\n");
        fwrite(STDERR, sprintf("  cp %s %s\n\n", $example, $projectRoot . '/dev/' . self::CONFIG_NAME));
    }

    /**
     * Разбирает значение заголовка `Host` на имя сервера и порт.
     *
     * @return array{string, string}
     */
    private static function splitHost(string $host, bool $isHttps): array
    {
        $position = strrpos($host, ':');

        if ($position !== false) {
            $port = substr($host, $position + 1);

            if ($port !== '' && ctype_digit($port)) {
                return [substr($host, 0, $position), $port];
            }
        }

        return [$host, $isHttps ? '443' : '80'];
    }
}
