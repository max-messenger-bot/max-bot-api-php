<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Dev;

use MaxMessenger\Bot\Exception\SimpleQueryError;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Model\Response\Update;
use Throwable;

use function dirname;
use function is_resource;
use function sprintf;
use function strlen;

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

        $script = $config['SCRIPT'] ?? null;
        $bin = $config['BIN'] ?? null;
        $secret = $config['MAXBOT_SECRET'] ?? null;

        if ($script === null) {
            fwrite(STDERR, sprintf("❌ Параметр SCRIPT не задан в %s\n", $configPath));
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

        $accessToken = $config['MAXBOT_ACCESS_TOKEN'] ?? Utils::requestAccessToken();

        Utils::printHeader('Polling → Webhook Bridge');
        echo sprintf("Config : %s\n", $configPath);
        echo sprintf("Script : %s\n", $script);
        echo sprintf("BIN    : %s\n", $bin);
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

    /**
     * Пути-кандидаты для поиска конфига: корень проекта и его папка dev/.
     *
     * @return list<string>
     */
    private static function configCandidates(): array
    {
        return Utils::projectFileCandidates(self::CONFIG_NAME);
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
}
