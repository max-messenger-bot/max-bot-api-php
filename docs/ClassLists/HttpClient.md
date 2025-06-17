# HTTP-клиент

## Список классов HTTP-клиента

### Основные классы

- [**JsonBody**](../../src/HttpClient/Body/JsonBody.php) — JSON-тело для HTTP-запросов.
- [**JsonRequest**](../../src/HttpClient/JsonRequest.php) — JSON HTTP-запрос.
- [**JsonResponse**](../../src/HttpClient/JsonResponse.php) — JSON HTTP-ответ.
- [**MaxHttpClient**](../../src/HttpClient/MaxHttpClient.php) — HTTP-клиент для API Max.
- [**MaxUploadHttpClient**](../../src/HttpClient/MaxUploadHttpClient.php) — HTTP-клиент для загрузки файлов.

### Загрузка файлов

- [**ResumableInfoRequest**](../../src/HttpClient/Upload/ResumableInfoRequest.php) — HTTP-запрос для получения информации о загружаемом
  файле.
- [**ResumableInfoResponse**](../../src/HttpClient/Upload/ResumableInfoResponse.php) — HTTP-ответ для получения информации о загружаемом
  файле.
- [**ResumableUploadRequest**](../../src/HttpClient/Upload/ResumableUploadRequest.php) — HTTP-запрос для возобновляемой загрузки файла.
- [**ResumableUploadResponse**](../../src/HttpClient/Upload/ResumableUploadResponse.php) — HTTP-ответ для возобновляемой загрузки файла.
- [**SimpleUploadRequest**](../../src/HttpClient/Upload/SimpleUploadRequest.php) — HTTP-запрос для простой загрузки файла.
- [**SimpleUploadResponse**](../../src/HttpClient/Upload/SimpleUploadResponse.php) — HTTP-ответ для простой загрузки файла.

### Исключения

- [**AccessTokenException**](../../src/HttpClient/Exceptions/AccessTokenException.php) — Access token not set.
- [**UnexpectedFormatException**](../../src/HttpClient/Exceptions/UnexpectedFormatException.php) — Unexpected response format.
- [**UploadException**](../../src/HttpClient/Exceptions/HttpResponse/UploadException.php) — Upload error.

### HTTP-исключения

- [**BadRequestException**](../../src/HttpClient/Exceptions/HttpResponse/Http/BadRequestException.php) — Неверный запрос.
- [**ForbiddenException**](../../src/HttpClient/Exceptions/HttpResponse/Http/ForbiddenException.php) — Ошибка доступа.
- [**InternalHttpException**](../../src/HttpClient/Exceptions/HttpResponse/Http/InternalHttpException.php) — Внутренняя ошибка сервера.
- [**MaxHttpException**](../../src/HttpClient/Exceptions/HttpResponse/Http/MaxHttpException.php) (abstract) — Базовый класс для
  HTTP-исключений API Max.
- [**NotAllowedException**](../../src/HttpClient/Exceptions/HttpResponse/Http/NotAllowedException.php) — Метод не разрешён.
- [**NotFoundException**](../../src/HttpClient/Exceptions/HttpResponse/Http/NotFoundException.php) — Ресурс не найден.
- [**ServiceUnavailableException**](../../src/HttpClient/Exceptions/HttpResponse/Http/ServiceUnavailableException.php) — Сервис недоступен.
- [**TooManyRequestsException**](../../src/HttpClient/Exceptions/HttpResponse/Http/TooManyRequestsException.php) — Превышено количество
  запросов.
- [**UnauthorizedException**](../../src/HttpClient/Exceptions/HttpResponse/Http/UnauthorizedException.php) — Ошибка аутентификации.
- [**UnknownException**](../../src/HttpClient/Exceptions/HttpResponse/Http/UnknownException.php) — Неизвестная ошибка сервера.
- [**UnsupportedMediaTypeException**](../../src/HttpClient/Exceptions/HttpResponse/Http/UnsupportedMediaTypeException.php) —
  Неподдерживаемый тип данных.

### Соглашения

- Формат элемента списка:
    ```markdown
    - [**ClassName**](../../src/HttpClient/.../ClassName.php) — Короткое описание класса.
    ```
- Списки должны быть отсортированы по алфавиту
