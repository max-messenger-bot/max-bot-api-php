<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Enums;

/**
 * @api
 */
enum ButtonType: string
{
    case Callback = 'callback';
    case Chat = 'chat';
    case Link = 'link';
    case Message = 'message';
    case RequestContact = 'request_contact';
    case RequestGeoLocation = 'request_geo_location';
}
