# HTTP-клиент

## Список классов HTTP-клиента

### Основные классы

- [**JsonBody**](../../src/HttpClient/Body/JsonBody.php) — JSON-тело для HTTP-запросов.
- [**JsonRequest**](../../src/HttpClient/JsonRequest.php) — JSON HTTP-запрос.
- [**JsonResponse**](../../src/HttpClient/JsonResponse.php) — JSON HTTP-ответ.
- [**MaxHttpClient**](../../src/HttpClient/MaxHttpClient.php) — HTTP-клиент для API Max.

## Интерфейсы

- [**MaxHttpClientInterface**](../../src/Contracts/MaxHttpClientInterface.php) — Интерфейс HTTP-клиента для API Max.

### Исключения

- [**AccessTokenException**](../../src/Exceptions/AccessTokenException.php) — Access token not set.

### Исключения HTTP запросов

- [**JsonEncodeException**](../../src/Exceptions/HttpClient/HttpRequest/JsonEncodeException.php) — JSON encoding error.

### Исключения HTTP ответов

- [**HttpResponseException**](../../src/Exceptions/HttpClient/HttpResponse/HttpResponseException.php) (abstract) — Base class for HTTP response exceptions.
- [**ParseDataException**](../../src/Exceptions/HttpClient/HttpResponse/ParseDataException.php) (abstract) — Base class for data parsing exceptions.
- [**JsonDecodeException**](../../src/Exceptions/HttpClient/HttpResponse/JsonDecodeException.php) — JSON decode error.
- [**UnexpectedFormatException**](../../src/Exceptions/HttpClient/HttpResponse/UnexpectedFormatException.php) — Unexpected response format.
- [**UnexpectedContentTypeException**](../../src/Exceptions/HttpClient/HttpResponse/UnexpectedContentTypeException.php) — Unexpected Content-Type.

### HTTP-исключения

- [**BadRequestException**](../../src/HttpClient/Exceptions/HttpResponse/Http/BadRequestException.php) — Неверный запрос.
- [**ForbiddenException**](../../src/HttpClient/Exceptions/HttpResponse/Http/ForbiddenException.php) — Ошибка доступа.
- [**InternalHttpException**](../../src/HttpClient/Exceptions/HttpResponse/Http/InternalHttpException.php) — Внутренняя ошибка сервера.
- [**MaxHttpException**](../../src/HttpClient/Exceptions/HttpResponse/Http/MaxHttpException.php) (abstract) — Базовый класс для HTTP-исключений API Max.
- [**NotAllowedException**](../../src/HttpClient/Exceptions/HttpResponse/Http/NotAllowedException.php) — Метод не разрешён.
- [**NotFoundException**](../../src/HttpClient/Exceptions/HttpResponse/Http/NotFoundException.php) — Ресурс не найден.
- [**ServiceUnavailableException**](../../src/HttpClient/Exceptions/HttpResponse/Http/ServiceUnavailableException.php) — Сервис недоступен.
- [**TooManyRequestsException**](../../src/HttpClient/Exceptions/HttpResponse/Http/TooManyRequestsException.php) — Превышено количество запросов.
- [**UnauthorizedException**](../../src/HttpClient/Exceptions/HttpResponse/Http/UnauthorizedException.php) — Ошибка аутентификации.
- [**UnknownException**](../../src/HttpClient/Exceptions/HttpResponse/Http/UnknownException.php) — Неизвестная ошибка сервера.
- [**UnsupportedMediaTypeException**](../../src/HttpClient/Exceptions/HttpResponse/Http/UnsupportedMediaTypeException.php) — Неподдерживаемый тип данных.

### Соглашения

- Формат элемента списка:
    ```markdown
    - [**ClassName**](../../src/HttpClient/.../ClassName.php) — Короткое описание класса.
    ```
- Списки должны быть отсортированы по алфавиту
