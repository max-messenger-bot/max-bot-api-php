<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exceptions\HttpClient\HttpResponse;

/**
 * Base class for data parsing exceptions.
 *
 * Abstract exception for handling data parsing errors from HTTP responses.
 */
abstract class ParseDataException extends HttpResponseException
{
}
