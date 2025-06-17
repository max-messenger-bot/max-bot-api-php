# Классы исключений

## Расположение исключений

Исключения расположены в следующих директориях:

- `src/Exceptions/` — Основные исключения библиотеки
- `src/Exceptions/Validation/` — Исключения валидации
- `src/Exceptions/MaxBot/Events/` — Исключения событий бота
- `src/Exceptions/MaxBot/Update/` — Исключения обновлений
- `src/Exceptions/HttpClient/HttpRequest/` — Исключения HTTP-запросов
- `src/Exceptions/HttpClient/HttpResponse/` — Исключения HTTP-ответов
- `src/HttpClient/Exceptions/HttpResponse/Http/` — HTTP-исключения Max API

## Список классов исключений

План проверки классов.

### Корневые исключения

- ✅ [**AccessTokenException**](../../src/Exceptions/AccessTokenException.php) — Access token not set.
- ✅ [**ActionProhibited**](../../src/Exceptions/ActionProhibited.php) — Action prohibited.
- ✅ [**MaxApiException**](../../src/Exceptions/MaxApiException.php) (abstract) — Base class for runtime exceptions.
- ✅ [**MaxApiLogicException**](../../src/Exceptions/MaxApiLogicException.php) (abstract) — Base class for logic exceptions.
- ✅ [**RequiredArgumentException**](../../src/Exceptions/RequiredArgumentException.php) — Argument cannot be null.
- ✅ [**RequiredArgumentsException**](../../src/Exceptions/RequiredArgumentsException.php) — At least one argument must be non-null.
- ✅ [**SimpleQueryError**](../../src/Exceptions/SimpleQueryError.php) — Simple query error.

### Validation — Validation exceptions

- ✅ [**KeyboardException**](../../src/Exceptions/Validation/KeyboardException.php) — Keyboard validation exception.
- ✅ [**MatchException**](../../src/Exceptions/Validation/MatchException.php) — Argument has an invalid value.
- ✅ [**MaximumException**](../../src/Exceptions/Validation/MaximumException.php) — Argument exceeds maximum value.
- ✅ [**MaxItemsException**](../../src/Exceptions/Validation/MaxItemsException.php) — Array exceeds maximum item count.
- ✅ [**MaxLengthException**](../../src/Exceptions/Validation/MaxLengthException.php) — String exceeds maximum length.
- ✅ [**MinimumException**](../../src/Exceptions/Validation/MinimumException.php) — Argument is below minimum value.
- ✅ [**MinItemsException**](../../src/Exceptions/Validation/MinItemsException.php) — Array has insufficient items.
- ✅ [**MinLengthException**](../../src/Exceptions/Validation/MinLengthException.php) — String is too short.
- ✅ [**MustBeLessException**](../../src/Exceptions/Validation/MustBeLessException.php) — Argument must be less than another.
- ✅ [**RequiredFieldException**](../../src/Exceptions/Validation/RequiredFieldException.php) — Field cannot be null.
- ✅ [**RequiredOneFieldException**](../../src/Exceptions/Validation/RequiredOneFieldException.php) — At least one field must be non-null.
- ✅ [**ValidationException**](../../src/Exceptions/Validation/ValidationException.php) (abstract) — Base class for validation exceptions.

### MaxBot/Events — Event-related exceptions

- ✅ [**EventException**](../../src/Exceptions/MaxBot/Events/EventException.php) — Служебное исключение для завершения обработки.
- ✅ [**SenderUnknownException**](../../src/Exceptions/MaxBot/Events/SenderUnknownException.php) — The sender is unknown.

### MaxBot/Update — Update-related exceptions

- ✅ [**BadRequestException**](../../src/Exceptions/MaxBot/Update/BadRequestException.php) — Bad request.
- ✅ [**InvalidSecretException**](../../src/Exceptions/MaxBot/Update/InvalidSecretException.php) — Invalid Secret.
- ✅ [**UpdateRequestException**](../../src/Exceptions/MaxBot/Update/UpdateRequestException.php) (abstract) — Base class for update request exceptions.

### HttpClient/HttpRequest — HTTP request exceptions

- ✅ [**JsonEncodeException**](../../src/Exceptions/HttpClient/HttpRequest/JsonEncodeException.php) — JSON encoding error.

### HttpClient/HttpResponse — HTTP response exceptions

- ✅ [**HttpResponseException**](../../src/Exceptions/HttpClient/HttpResponse/HttpResponseException.php) (abstract) — Base class for HTTP response exceptions.
- ✅ [**ParseDataException**](../../src/Exceptions/HttpClient/HttpResponse/ParseDataException.php) (abstract) — Base class for data parsing exceptions.
- ✅ [**JsonDecodeException**](../../src/Exceptions/HttpClient/HttpResponse/JsonDecodeException.php) — JSON decode error.
- ✅ [**UnexpectedFormatException**](../../src/Exceptions/HttpClient/HttpResponse/UnexpectedFormatException.php) — Unexpected response format.
- ✅ [**UnexpectedContentTypeException**](../../src/Exceptions/HttpClient/HttpResponse/UnexpectedContentTypeException.php) — Unexpected Content-Type.

### HttpClient/Exceptions/HttpResponse/Http — HTTP exceptions Max API

- ✅ [**BadRequestException**](../../src/HttpClient/Exceptions/HttpResponse/Http/BadRequestException.php) — Неверный запрос.
- ✅ [**ForbiddenException**](../../src/HttpClient/Exceptions/HttpResponse/Http/ForbiddenException.php) — Ошибка доступа.
- ✅ [**InternalHttpException**](../../src/HttpClient/Exceptions/HttpResponse/Http/InternalHttpException.php) — Внутренняя ошибка сервера.
- ✅ [**MaxHttpException**](../../src/HttpClient/Exceptions/HttpResponse/Http/MaxHttpException.php) (abstract) — Базовый класс для HTTP-исключений Max API.
- ✅ [**NotAllowedException**](../../src/HttpClient/Exceptions/HttpResponse/Http/NotAllowedException.php) — Метод не разрешён.
- ✅ [**NotFoundException**](../../src/HttpClient/Exceptions/HttpResponse/Http/NotFoundException.php) — Ресурс не найден.
- ✅ [**ServiceUnavailableException**](../../src/HttpClient/Exceptions/HttpResponse/Http/ServiceUnavailableException.php) — Сервис недоступен.
- ✅ [**TooManyRequestsException**](../../src/HttpClient/Exceptions/HttpResponse/Http/TooManyRequestsException.php) — Превышено количество запросов.
- ✅ [**UnauthorizedException**](../../src/HttpClient/Exceptions/HttpResponse/Http/UnauthorizedException.php) — Ошибка аутентификации.
- ✅ [**UnknownException**](../../src/HttpClient/Exceptions/HttpResponse/Http/UnknownException.php) — Неизвестная ошибка сервера.
- ✅ [**UnsupportedMediaTypeException**](../../src/HttpClient/Exceptions/HttpResponse/Http/UnsupportedMediaTypeException.php) — Неподдерживаемый тип данных.

### Соглашения

- Формат элемента списка:
    ```markdown
    - ❌ [**ExceptionName**](../../src/Exceptions/ExceptionName.php) — Короткое описание класса
    ```
- Формат элемента списка с вложенными элементами:
    ```markdown
    - **FolderName** — Короткое описание папки
    ```

- Добавляемые классы исключений, нужно отмечать символом `❌`
- Список должен быть отсортирован по алфавиту
- **Отметка `✅` ставится при выполнении всех условий:**
    - Есть описание класса на английском языке.
    - Все элементы класса кроме конструктора имеют комментарии на английском языке.
        - В `EventException.php` описание и комментарии на русском языке.
        - В классах из `src/HttpClient/Exceptions/HttpResponse/Http/` описания могут быть на русском языке, так как они взяты из схемы API.
