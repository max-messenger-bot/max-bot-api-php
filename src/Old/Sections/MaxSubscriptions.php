<?php

declare(strict_types=1);

namespace MaxMessenger\Api\Old\Sections;

use MaxMessenger\Api\Modules\ModuleTrait;
use MaxMessenger\Api\Old\Models\Enums\UpdateType;
use MaxMessenger\Api\Old\Models\Request\Subscriptions\DeleteSubscriptions;
use MaxMessenger\Api\Old\Models\Request\Subscriptions\GetUpdates;
use MaxMessenger\Api\Old\Models\Request\Subscriptions\PostSubscriptions;
use MaxMessenger\Api\Old\Models\Response\Shared\Result;
use MaxMessenger\Api\Old\Models\Response\Subscriptions\Subscriptions;

/**
 * @api
 */
final class MaxSubscriptions
{
    use ModuleTrait;

    /**
     * Получение подписок
     *
     * Если ваш бот получает данные через WebHook, этот метод возвращает список всех подписок.
     *
     * @link https://dev.max.ru/docs-api/methods/GET/subscriptions
     */
    public function getSubscriptions(): Subscriptions
    {
        return new Subscriptions($this->client->get('/subscriptions'));
    }

    /**
     * Получение обновлений
     *
     * Этот метод можно использовать для получения обновлений, если ваш бот не подписан на WebHook.
     * Метод использует долгий опрос (long polling).
     *
     * Каждое обновление имеет свой номер последовательности.
     * Свойство `marker` в ответе указывает на следующее ожидаемое обновление.
     *
     * Все предыдущие обновления считаются завершенными после прохождения параметра `marker`.
     * Если параметр `marker` не передан, бот получит все обновления, произошедшие после последнего подтверждения.
     *
     * @param int<1,1000>|null $limit Максимальное количество обновлений для получения. По умолчанию: 100.
     * @param int<0,90>|null $timeout Тайм-аут в секундах для долгого опроса. По умолчанию: 30.
     * @param int|null $marker Если передан, бот получит обновления, которые еще не были получены.
     *                         Если не передан, получит все новые обновления.
     * @param array<UpdateType|string>|null $types Список типов обновлений, которые бот хочет получить.
     *
     * @link https://dev.max.ru/docs-api/methods/GET/updates
     */
    public function getUpdates(?int $limit, ?int $timeout, ?int $marker, ?array $types): Subscriptions
    {
        $request = new GetUpdates($limit, $timeout, $marker, $types);

        return new Subscriptions($this->client->get('/updates', $request));
    }

    /**
     * Подписка на обновления
     *
     * Подписывает бота на получение обновлений через WebHook.
     * После вызова этого метода бот будет получать уведомления о новых событиях в чатах на указанный URL.
     * Ваш сервер должен прослушивать один из следующих портов: 80, 8080, 443, 8443, 16384-32383.
     *
     * @param string $url URL HTTP(S)-эндпойнта вашего бота. Должен начинаться с `http(s)://`.
     * @param array<UpdateType|string>|null $updateTypes Список типов обновлений, которые ваш бот хочет получать.
     *
     * @link https://dev.max.ru/docs-api/methods/POST/subscriptions
     */
    public function subscribe(string $url, ?array $updateTypes): Result
    {
        $request = new PostSubscriptions($url, $updateTypes);

        return new Result($this->client->patch('/subscriptions', $request));
    }

    /**
     * Отписка от обновлений
     *
     * Отписывает бота от получения обновлений через WebHook.
     * После вызова этого метода бот перестает получать уведомления о новых событиях,
     * и доступна доставка уведомлений через API с длительным опросом.
     *
     * @param string $url URL, который нужно удалить из подписок на WebHook.
     *
     * @link https://dev.max.ru/docs-api/methods/DELETE/subscriptions
     */
    public function unsubscribe(string $url): Result
    {
        $request = new DeleteSubscriptions($url);

        return new Result($this->client->delete('/subscriptions', $request));
    }
}
