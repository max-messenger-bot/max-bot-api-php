<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Статистика сообщения.
 *
 * Возвращается только для постов в каналах.
 */
class MessageStat extends BaseResponseModel
{
    /**
     * @var array{
     *     views: int
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return int Количество пользователей, которые увидели пост в канале.
     *     Просмотр засчитывается, когда пост попадает в область видимости экрана.
     */
    public function getViews(): int
    {
        return $this->data['views'];
    }
}
