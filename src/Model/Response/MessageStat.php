<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Статистика просмотров постов и репостов.
 *
 * Возвращается только для каналов.
 *
 * @link https://dev.max.ru/docs-api/objects/Message/
 */
class MessageStat extends BaseResponseModel
{
    /**
     * @var array{
     *     views: non-negative-int
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return non-negative-int Количество пользователей, которые увидели пост или репост в канале.
     *     Просмотр засчитывается, когда пост или репост попадает в область видимости экрана.
     *     Если это репост, будет показано количество именно его просмотров.
     */
    public function getViews(): int
    {
        return $this->data['views'];
    }
}
