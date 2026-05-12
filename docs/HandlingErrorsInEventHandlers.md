# Обработка ошибок в обработчиках событий

## Введение

В процессе обработки событий могут возникать различные ошибки: от сетевых проблем при вызове API до логических ошибок в
коде обработчика. Правильная обработка ошибок позволяет вашему боту gracefully восстанавливаться после сбоев и не
прерывать обработку других событий.

**Связанная документация:**

- [Обработка событий](ProcessingEvents.md)

## Базовая обработка исключений

### Регистрация обработчика исключений

Для обработки исключений используйте метод `onException()`:

```php
use MaxMessenger\Bot\MaxBot;
use MaxMessenger\Bot\MaxBot\Events\BaseEvent;
use Throwable;

$bot = new MaxBot('your-access-token', 'your-secret');

$bot->onException(function (Throwable $exception, BaseEvent $event): bool {
    // Логирование ошибки
    error_log("Ошибка при обработке события: " . $exception->getMessage());

    // Отмечаем событие как обработанное, чтобы исключение не было выброшено
    return true;
});
```

### Поведение без обработчика исключений

Если обработчик исключений не зарегистрирован или не изменил статус события:

- Исключение будет выброшено наружу
- Дальнейшая обработка событий прекратится

### Поведение с обработчиком исключений

Когда обработчики исключений зарегистрированы:

- **Все** обработчики из списка `Exception Handlers` будут вызваны независимо от статуса события
- При возврате обработчиком `true` или вызове метода `Event::break()`, после вызова всех обработчиков, 
  событие будет отмечено как обработанное
- Если ни один обработчик не вернул `true` или `false` и не вызвал соответствующие методы `Event`,
  исключение будет выброшено дальше
- При вызове каждого обработчика `$event->isHandled` будет содержать актуальное значение на момент
  возникновения исключения, любые его изменения будут проигнорированы

## Стратегии обработки ошибок

### Стратегия 1: Логирование и отметка как обработанное

Записываем ошибку в лог и отмечаем событие как обработанное, чтобы обработка других событий продолжилась:

```php
$bot->onException(function (Throwable $exception, BaseEvent $event): bool {
    // Логирование для отладки
    error_log(sprintf(
        "[%s] Ошибка: %s в %s:%d",
        date('Y-m-d H:i:s'),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine()
    ));

    // Отмечаем событие как обработанное, обработка других событий продолжится
    return true;
});
```

### Стратегия 2: Логирование и продолжение обработки другими обработчиками

Записываем ошибку в лог и продолжаем обработку текущего события другими обработчиками:

```php
$bot->onException(function (Throwable $exception, BaseEvent $event): bool {
    // Логирование для отладки
    error_log(sprintf(
        "[%s] Ошибка: %s в %s:%d",
        date('Y-m-d H:i:s'),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine()
    ));

    // Помечаем событие как не обработанное для продолжения обработки другими обработчиками
    return false;
});
```

### Стратегия 3: Graceful degradation

При критических ошибках переходим в режим ограниченной функциональности:

```php
$emergencyMode = false;

$bot->onException(function (Throwable $exception, BaseEvent $event) use (&$emergencyMode): bool {
    if ($emergencyMode) {
        // Уже в аварийном режиме - просто логируем
        return true;
    }

    // Переходим в аварийный режим
    $emergencyMode = true;
    error_log("КРИТИЧЕСКАЯ ОШИБКА: Переход в аварийный режим. " . $exception->getMessage());

    return true; // Отмечаем событие как обработанное
});
```

## Определение контекста ошибки

Вы можете получить информацию о том, где произошла ошибка:

```php
use MaxMessenger\Bot\MaxBot\HandlerListType;

$bot->onException(function (Throwable $exception, BaseEvent $event): bool {
    $context = [
        'time' => date('Y-m-d H:i:s'),
        'eventTime' => $event->getTimestamp()->format('Y-m-d H:i:s'),
        'handlerList' => $event->currentHandlerListType?->value ?? 'unknown',
        'eventClass' => get_class($event),
        'exception' => $exception::class,
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
    ];

    // Логируем контекст ошибки
    error_log(json_encode($context, JSON_PRETTY_PRINT));

    return true; // Отмечаем событие как обработанное
});
```

### HandlerListType

Свойство `$event->currentHandlerListType` указывает, в каком списке произошла ошибка:

- `prepare` — ошибка в предварительных обработчиках
- `event` — ошибка в обработчиках событий по классу
- `typed` — ошибка в обработчиках по типу обновления
- `fallback` — ошибка в fallback обработчиках
- `final` — ошибка в финальных обработчиках
- `exception` — ошибка в обработчиках исключений

## Использование EventException для управления потоком

Вы можете использовать `EventException` для явного управления потоком обработки из обработчика исключений:

```php
use MaxMessenger\Bot\Exceptions\MaxBot\Events\EventException;
use MaxMessenger\Bot\MaxBot\Events\Event;

$bot->onException(function (Throwable $exception, BaseEvent $event): void {
    // Логирование
    error_log("Ошибка: " . $exception->getMessage());

    if ($exception instanceof \PDOException) {
        // Критическая ошибка БД - прекращаем обработку события
        Event::break();
    }

    // Для остальных ошибок - продолжаем обработку
    Event::continue();
});
```

## Практические рекомендации

### 1. Всегда логируйте ошибки

```php
$bot->onException(function (Throwable $exception, BaseEvent $event): bool {
    // Минимальное логирование
    error_log(sprintf(
        "[%s][%s] %s в %s:%d",
        $event::class,
        $exception::class,
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine()
    ));

    return true; // Отмечаем событие как обработанное
});
```

### 2. Разделяйте критические и некритические ошибки

```php
$bot->onException(function (Throwable $exception, BaseEvent $event): bool {
    // Критические ошибки
    if ($exception instanceof \PDOException || $exception instanceof \Error) {
        error_log("КРИТИЧЕСКАЯ ОШИБКА: " . $exception->getMessage());
        return true; // Отмечаем событие как обработанное
    }

    // Некритические ошибки
    error_log("Ошибка (некритическая): " . $exception->getMessage());
    return false; // Продолжаем обработку другими обработчиками
});
```

### 3. Используйте userData для отладки

```php
$bot->onPrepare(function (BaseEvent $event): void {
    $event->userData['startTime'] = microtime(true);
});

$bot->onException(function (Throwable $exception, BaseEvent $event): bool {
    $duration = microtime(true) - ($event->userData['startTime'] ?? 0);
    
    error_log(sprintf(
        "Ошибка после %.3f сек: %s",
        $duration,
        $exception->getMessage()
    ));

    return true; // Отмечаем событие как обработанное
});
```

### 4. Не игнорируйте ошибки полностью

```php
// ❌ ПЛОХО: Полное игнорирование ошибок
$bot->onException(function (Throwable $exception, BaseEvent $event): bool {
    return true; // Просто скрываем ошибку
});

// ✅ ХОРОШО: Логирование и обработка
$bot->onException(function (Throwable $exception, BaseEvent $event): bool {
    error_log("Ошибка: " . $exception->getMessage());
    return true; // Отмечаем событие как обработанное
});
```

## Пример полной обработки ошибок

```php
use MaxMessenger\Bot\MaxBot;
use MaxMessenger\Bot\MaxBot\Events\BaseEvent;
use MaxMessenger\Bot\HttpClient\Exceptions\HttpResponse\Http\MaxHttpException;
use Throwable;

$bot = new MaxBot('your-access-token', 'your-secret');

// Логирование всех ошибок
$bot->onException(function (Throwable $exception, BaseEvent $event): bool {
    $errorInfo = [
        'time' => date('Y-m-d H:i:s'),
        'event' => get_class($event),
        'handlerList' => $event->currentHandlerListType?->value ?? 'unknown',
        'exception' => get_class($exception),
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
    ];

    // Логирование
    error_log(json_encode($errorInfo));

    // Специфичная обработка HTTP-ошибок
    if ($exception instanceof MaxHttpException) {
        if (str_contains($exception->getMessage(), '401')) {
            // Ошибка авторизации - критично
            return true; // Отмечаем событие как обработанное
        }
        
        if (str_contains($exception->getMessage(), '429')) {
            // Превышен лимит - можно продолжить
            return false; // Продолжаем обработку другими обработчиками
        }
    }

    // Для остальных ошибок - логируем и отмечаем как обработанное
    return true;
});
```

## Обработка ошибок в production

Для production-окружения рекомендуется:

1. **Использовать систему логирования** (Monolog, etc.) вместо `error_log()`
2. **Отправлять уведомления** о критических ошибках (email, webhook, etc.)
3. **Реализовать retry-логику** для временных ошибок
4. **Мониторить количество ошибок** для выявления проблем
5. **Не показывать детали ошибок** в ответах

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$log = new Logger('bot');
$log->pushHandler(new StreamHandler('/var/log/bot/errors.log', Logger::WARNING));

$bot->onException(function (Throwable $exception, BaseEvent $event) use ($log): bool {
    $log->error('Ошибка при обработке события', [
        'event' => get_class($event),
        'exception' => get_class($exception),
        'message' => $exception->getMessage(),
        'trace' => $exception->getTraceAsString(),
    ]);

    // Отмечаем событие как обработанное
    return true;
});
```
