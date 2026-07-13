<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit;

use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxApiConfig;
use Mj4444\SimpleHttpClient\Exceptions\HttpClientException;
use Mj4444\SimpleHttpClient\Exceptions\HttpResponse\Http\HttpException;

use function stripos;

/**
 * Интеграционный тест: реальное обращение к API Max.
 *
 * @group network
 */
final class MaxApiConfigRealApiTest extends Unit
{
    /**
     * С включённым CA-бандлом Минцифры TLS-проверка сертификата `platform-api2.max.ru` должна пройти,
     * а ответом на запрос с невалидным токеном должна быть HTTP-ошибка, а не ошибка сертификата.
     */
    public function testUseRussianTrustedCaCertificatesReachesApiOverTls(): void
    {
        $config = new MaxApiConfig('invalid-access-token');
        $config->useRussianTrustedCaCertificates();
        $config->setRetryAttempts([]);

        $apiClient = new MaxApiClient($config);

        try {
            $apiClient->getMyInfo();
            self::fail('Ожидалась HTTP-ошибка от API при невалидном токене');
        } catch (HttpException $e) {
            // Дошли до HTTP-слоя (например, 401) — значит TLS-проверка по бандлу Минцифры прошла успешно.
            self::assertGreaterThanOrEqual(400, $e->getCode());
        } catch (HttpClientException $e) {
            // HTTP-ответа нет — это транспортная ошибка. Отличаем ошибку сертификата от отсутствия сети.
            $message = $e->getMessage();
            if (stripos($message, 'SSL') !== false || stripos($message, 'certificate') !== false) {
                self::fail('Получена ошибка сертификата вместо HTTP-ответа: ' . $message);
            }

            self::markTestSkipped('Нет сетевого доступа к API Max: ' . $message);
        }
    }
}
