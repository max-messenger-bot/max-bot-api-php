# HTTP-клиент

## Список классов HTTP-клиента

### Основные классы

- [**JsonRequest**](../src/HttpClient/JsonRequest.php) — JSON HTTP-запрос.
- [**JsonResponse**](../src/HttpClient/JsonResponse.php) — JSON HTTP-ответ.
- [**MaxHttpClient**](../src/HttpClient/MaxHttpClient.php) — HTTP-клиент для API Max.

### Исключения

- [**AccessTokenException**](../src/HttpClient/Exceptions/AccessTokenException.php) — Исключение ошибки токена доступа.
- [**UnexpectedFormatException**](../src/HttpClient/Exceptions/UnexpectedFormatException.php) — Исключение неожиданного
  формата ответа.

### HTTP-исключения

- [**ForbiddenException**](../src/HttpClient/Exceptions/HttpExceptions/ForbiddenException.php) — Исключение запрета
  доступа.
- [**InternalHttpException**](../src/HttpClient/Exceptions/HttpExceptions/InternalHttpException.php) — Исключение
  внутренней ошибки сервера.
- [**MaxHttpException**](../src/HttpClient/Exceptions/HttpExceptions/MaxHttpException.php) — Базовый класс для
  HTTP-исключений Max API.
- [**NotAllowedException**](../src/HttpClient/Exceptions/HttpExceptions/NotAllowedException.php) — Исключение
  запрещённого метода.
- [**NotFoundException**](../src/HttpClient/Exceptions/HttpExceptions/NotFoundException.php) — Исключение ресурса не
  найден.
- [**UnauthorizedException**](../src/HttpClient/Exceptions/HttpExceptions/UnauthorizedException.php) — Исключение
  ошибки авторизации.
- [**UnknownException**](../src/HttpClient/Exceptions/HttpExceptions/UnknownException.php) — Исключение неизвестной
  ошибки сервера.

### Конвенции

- Формат элемента списка:
    ```markdown
    - [**ClassName**](../src/HttpClient/.../ClassName.php) — Короткое описание класса.
    ```
- Списки должны быть отсортированы по алфавиту
