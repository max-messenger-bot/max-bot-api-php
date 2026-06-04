<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use MaxMessenger\Bot\Dev\Utils;
use MaxMessenger\Bot\Exception\SimpleQueryError;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Model\Response\Update;

/**
 * Long Polling → Webhook Bridge
 *
 * Получает события через Long Polling и передаёт их скрипту-обработчику
 * через php-cgi, эмулируя входящий Webhook-запрос.
 */
final class PollingToWebhook
{
    private const CONFIG_FILE = __DIR__ . '/../dev/.polling-to-webhook.conf';

    public static function main(): void
    {
        $config = Utils::loadConfigFromFile(self::CONFIG_FILE);

        $script = $config['SCRIPT'] ?? null;
        $bin = $config['BIN'] ?? null;
        $secret = $config['SECRET'] ?? null;

        if ($script === null) {
            fwrite(STDERR, sprintf("❌ Параметр SCRIPT не задан в %s\n", self::CONFIG_FILE));
            exit(1);
        }

        if ($bin === null) {
            fwrite(STDERR, sprintf("❌ Параметр BIN не задан в %s\n", self::CONFIG_FILE));
            exit(1);
        }

        if ($secret === null) {
            fwrite(STDERR, sprintf("❌ Параметр SECRET не задан в %s\n", self::CONFIG_FILE));
            exit(1);
        }

        $apiKey = isset($config['API_KEY']) ? $config['API_KEY'] : Utils::requestApiKey();

        Utils::printHeader('Polling → Webhook Bridge');
        echo sprintf("Script : %s\n", $script);
        echo sprintf("BIN    : %s\n", $bin);
        echo "\nНажмите Ctrl+C для остановки\n";
        Utils::printLine(true);

        $client = new MaxApiClient($apiKey);
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
                    self::dispatchUpdate($update, $script, $bin, $secret);
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

    private static function dispatchUpdate(Update $update, string $script, string $bin, string $secret): void
    {
        $body = json_encode($update->getRawData(), JSON_UNESCAPED_UNICODE);

        if ($body === false) {
            fwrite(STDERR, "❌ Ошибка сериализации события\n");

            return;
        }

        $contentLength = strlen($body);

        $env = [
            'REDIRECT_STATUS' => '200',
            'REQUEST_METHOD' => 'POST',
            'SCRIPT_FILENAME' => $script,
            'SCRIPT_NAME' => basename($script),
            'QUERY_STRING' => '',
            'CONTENT_TYPE' => 'application/json; charset=UTF-8',
            'CONTENT_LENGTH' => (string) $contentLength,
            'HTTP_CONNECTION' => 'close',
            'HTTP_X_MAX_BOT_API_SECRET' => $secret,
            'HTTP_USER_AGENT' => 'OneMe/0.1.10 Bot API',
        ];

        Utils::printLine();
        echo sprintf("Событие: %s\n", $update->getUpdateTypeRaw());
        Utils::printLine();

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => STDOUT,
            2 => STDERR,
        ];

        $process = proc_open([$bin, $script], $descriptors, $pipes, null, $env); // nosemgrep

        if (!is_resource($process)) {
            fwrite(STDERR, "❌ Не удалось запустить скрипт\n");

            return;
        }

        fwrite($pipes[0], $body);
        fclose($pipes[0]);
        proc_close($process);
    }
}

PollingToWebhook::main();
