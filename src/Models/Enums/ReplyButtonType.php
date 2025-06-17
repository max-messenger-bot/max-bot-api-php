<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Enums;

/**
 * Type of reply button.
 *
 * @api
 */
enum ReplyButtonType: string
{
    case Message = 'message';
    case UserContact = 'user_contact';
    case UserGeoLocation = 'user_geo_location';
}
