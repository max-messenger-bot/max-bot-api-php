<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Enum;

/**
 * Тип кнопки.
 */
enum ButtonType: string
{
    case Callback = 'callback';
    case Chat = 'chat';
    case Clipboard = 'clipboard';
    case Link = 'link';
    case Message = 'message';
    case OpenApp = 'open_app';
    case RequestContact = 'request_contact';
    case RequestGeoLocation = 'request_geo_location';
}
