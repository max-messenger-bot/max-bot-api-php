# Примеры обработки событий

## Введение

В этом документе представлены примеры обработки различных типов событий ботом MaxBot.

**Связанная документация:**

- [Обработка событий](../ProcessingEvents.md)
- [Обработка нажатий кнопок](../ProcessingCallbacks.md)
- [Обработка команд](../ProcessingCommands.md)
- [Примеры обработки нажатий кнопок](ProcessingCallbacks.md)
- [Примеры обработки команд](ProcessingCommands.md)

## Базовые примеры

### Обработка новых сообщений

Простой пример обработки входящих сообщений:

```php
use MaxMessenger\Bot\MaxBot;
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;

$bot = new MaxBot('your-access-token', 'your-secret');

$bot->onMessageCreated(function (MessageCreatedEvent $event): bool {
    $message = $event->getMessage();
    $text = $message->getText();
    
    // Отвечаем на сообщение
    $event->reply('Вы написали: ' . $text);

    return true; // Отмечаем событие как обработанное
});
```

### Обработка команды /start

Использование события BotStartedEvent:

```php
use MaxMessenger\Bot\MaxBot\Event\BotStartedEvent;

$bot->onBotStarted(function (BotStartedEvent $event): bool {
    $user = $event->getUser();
    $payload = $event->getPayload(); // Данные из дип-линка
    
    $welcomeText = 'Привет, ' . $user->getFirstName() . '!';
    
    if ($payload !== null) {
        $welcomeText .= "\nВы пришли по ссылке с параметром: " . $payload;
    }
    
    $event->sendToUser($welcomeText);

    return true; // Отмечаем событие как обработанное
});
```

## Работа с разными типами событий

### Обработка нескольких типов сообщений

```php
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageEditedEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageRemovedEvent;

// Новое сообщение
$bot->onMessageCreated(function (MessageCreatedEvent $event): bool {
    $text = $event->getMessage()->getText();
    
    // Логируем сообщение
    error_log("Новое сообщение: " . $text);

    return true;
});

// Редактирование сообщения
$bot->onMessageEdited(function (MessageEditedEvent $event): bool {
    $text = $event->getMessage()->getText();
    
    error_log("Сообщение отредактировано: " . $text);

    return true;
});

// Удаление сообщения
$bot->onMessageRemoved(function (MessageRemovedEvent $event): bool {
    $messageId = $event->getMessageId();
    $userId = $event->getUserId();
    
    error_log("Сообщение $messageId удалено пользователем $userId");

    return true;
});
```

### Обработка изменений в чате

```php
use MaxMessenger\Bot\MaxBot\Event\ChatTitleChangedEvent;
use MaxMessenger\Bot\MaxBot\Event\DialogClearedEvent;
use MaxMessenger\Bot\MaxBot\Event\UserAddedToChatEvent;

// Изменение заголовка чата
$bot->onChatTitleChanged(function (ChatTitleChangedEvent $event): bool {
    $title = $event->getTitle();
    $user = $event->getUser();
    
    error_log("Пользователь {$user->getFirstName()} изменил название чата на '$title'");

    return true;
});

// Очистка диалога
$bot->onDialogCleared(function (DialogClearedEvent $event): bool {
    $user = $event->getUser();
    
    error_log("Пользователь {$user->getFirstName()} очистил историю диалога");

    return true;
});

// Добавление пользователя в чат
$bot->onUserAddedToChat(function (UserAddedToChatEvent $event): bool {
    $user = $event->getUser();
    $inviterId = $event->getInviterId();
    
    if ($inviterId !== null) {
        error_log("Пользователь {$user->getFirstName()} добавлен в чат пользователем $inviterId");
    } else {
        error_log("Пользователь {$user->getFirstName()} присоединился к чату по ссылке");
    }

    return true;
});
```

## Использование обработчиков разных типов

### onPrepare — предварительная обработка

```php
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;

// Логирование всех входящих событий
$bot->onPrepare(function (BaseEvent $event): void {
    $event->userData['receivedAt'] = time();
    $event->userData['startTime'] = microtime(true);
    
    error_log("Получено событие: " . get_class($event));
});
```

### on — обработка по типу обновления

```php
use MaxMessenger\Bot\Model\Enum\UpdateType;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;

// Обработка всех сообщений о создании диалогов
$bot->on(UpdateType::BotStarted, function (BaseEvent $event): bool {
    // Обработка события запуска бота

    return true;
});
```

### onFallback — обработка необработанных событий

```php
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;

// Ловим все события, которые не были обработаны другими обработчиками
$bot->onFallback(function (BaseEvent $event): bool {
    error_log("Необработанное событие: " . $event::class);

    return true; // Отмечаем как обработанное, чтобы не было ошибок
});
```

### onFinal — финальная обработка

```php
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;

// Финальная обработка для статистики
$bot->onFinal(function (BaseEvent $event): void {
    if (isset($event->userData['startTime'])) {
        $duration = microtime(true) - $event->userData['startTime'];
        $handled = $event->handledIn !== null ? 'да' : 'нет';
        
        error_log(sprintf(
            "Обработка завершена за %.3f сек. Обработано: %s",
            $duration,
            $handled
        ));
    }
});
```

## Работа со статусами событий

### Отметка события как обработанного

```php
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;

$bot->onMessageCreated(function (MessageCreatedEvent $event): bool {
    // Обрабатываем сообщение
    
    return true; // Событие обработано, другие обработчики не вызываются
});
```

### Отметка события как необработанного

```php
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;

$bot->onMessageCreated(function (MessageCreatedEvent $event): bool {
    // Проверяем, можем ли обработать
    if (!canProcessMessage($event)) {
        return false; // Событие не обработано, будут вызваны другие обработчики
    }
    
    // Обрабатываем сообщение

    return true;
});
```

### Использование методов break и continue

```php
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;

$bot->onMessageCreated(function (MessageCreatedEvent $event): bool {
    $text = $event->getMessage()->getText();
    
    if (str_contains($text, '/stop')) {
        // Прерываем обработку, событие считается обработанным
        $event->break();
    }
    
    if (str_contains($text, '/skip')) {
        // Прерываем обработку, событие считается необработанным
        $event->continue();
    }

    return true;
});
```

## Использование вспомогательных данных ($userData)

### Передача данных между обработчиками

```php
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;

// onPrepare сохраняет время получения
$bot->onPrepare(function (BaseEvent $event): void {
    $event->userData['receivedAt'] = time();
});

// Основной обработчик использует сохранённые данные
$bot->onMessageCreated(function (MessageCreatedEvent $event): bool {
    $receivedAt = $event->userData['receivedAt'] ?? null;
    $delay = time() - $receivedAt;
    
    if ($delay > 60) {
        error_log("Сообщение получено $delay секунд назад");
    }

    return true;
});
```

### Подсчёт обработанных сообщений

```php
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;

$messageCount = 0;

$bot->onMessageCreated(function (MessageCreatedEvent $event) use (&$messageCount): bool {
    $messageCount++;
    
    if ($messageCount % 100 === 0) {
        error_log("Обработано $messageCount сообщений");
    }

    return false;
});
```

## Примеры реакции на события

### Ответ на сообщение

```php
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;

$bot->onMessageCreated(function (MessageCreatedEvent $event): bool {
    $text = $event->getMessage()->getText();
    
    // Простой ответ
    $event->reply('Вы написали: ' . $text);
    
    // Ответ с цитированием
    $event->reply('Ответ на ваше сообщение', asReply: true);

    return true;
});
```

### Пересылка сообщения

```php
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;

$bot->onMessageCreated(function (MessageCreatedEvent $event): bool {
    $adminChatId = 12345;
    
    // Пересылаем сообщение в админ-чат
    $event->forwardToChat($adminChatId);

    return true;
});
```

### Пересылка сообщения пользователю

```php
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;

$bot->onMessageCreated(function (MessageCreatedEvent $event): bool {
    $text = $event->getMessage()->getText();

    // Если сообщение содержит важный контент, пересылаем его автору в диалог
    if (str_contains($text, '!важно')) {
        $event->forwardToUser($event->getUserId());
    }

    return true;
});
```

### Ответ пользователю в диалоге

```php
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;

$bot->onMessageCreated(function (MessageCreatedEvent $event): bool {
    $text = $event->getMessage()->getText();

    // Отвечаем пользователю лично в диалоге
    $event->replyToUser('Вы написали в чате: ' . $text);

    // Можно также переслать оригинальное сообщение
    $event->replyToUser('Дублирую ваше сообщение:', forwardOrigMessage: true);

    return true;
});
```

### Проверка типа чата

```php
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;

$bot->onMessageCreated(function (MessageCreatedEvent $event): bool {
    $text = $event->getMessage()->getText();

    // Разная логика для разных типов чатов
    if ($event->isDialog()) {
        // Обработка личных сообщений
        $event->reply('Личное сообщение: ' . $text);
    } elseif ($event->isChat()) {
        // Обработка сообщений из группы
        $event->reply('Сообщение в группе: ' . $text);
    } elseif ($event->isChannel()) {
        // Обработка сообщений из канала
        error_log('Новое сообщение в канале: ' . $text);
    }

    return true;
});
```

### Отправка сообщения пользователю в диалог

```php
use MaxMessenger\Bot\MaxBot\Event\UserAddedToChatEvent;

$bot->onUserAddedToChat(function (UserAddedToChatEvent $event): bool {
    $user = $event->getUser();
    
    // Отправляем приветственное сообщение пользователю в диалог
    $event->sendToUser('Добро пожаловать в чат, ' . $user->getFirstName() . '!');

    return true;
});
```

### Отправка сообщения в чат события

```php
use MaxMessenger\Bot\MaxBot\Event\ChatTitleChangedEvent;

$bot->onChatTitleChanged(function (ChatTitleChangedEvent $event): bool {
    $title = $event->getTitle();

    // Отправляем сообщение в чат, где произошло событие
    $event->sendToChat('Заголовок чата изменён на: ' . $title);

    return true;
});
```

### Удаление сообщения

```php
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;

$bot->onMessageCreated(function (MessageCreatedEvent $event): bool {
    $text = $event->getMessage()->getText();
    
    // Удаляем сообщения с определённым содержимым
    if (str_contains($text, 'спам')) {
        $event->deleteMessage();
    }

    return true;
});
```

### Обработка контакта пользователя

```php
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;
use MaxMessenger\Bot\Model\Response\ContactAttachment;

$bot->onMessageCreated(function (MessageCreatedEvent $event): bool {
    if ($event->isSelfContact()) {
        // Пользователь поделился своим реальным номером
        /** @var ContactAttachment $contact */
        $contact = $event->getMessage()->getBody()->getAttachments()[0];
        $phones = $contact->getPayload()->getPhones();
        if (count($phones) === 1) {
            // Обрабатываем номер телефона
            $phone = reset($phones);
        } else {
            // Номеров нет, либо их несколько = ошибка в логике?
        }

        return true;
    }

    return false;
});
```

## Полный пример бота

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use MaxMessenger\Bot\MaxBot;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\BotStartedEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;
use Throwable;

$bot = new MaxBot('your-access-token', 'your-secret');

// Предварительная обработка — логирование
$bot->onPrepare(function (BaseEvent $event): void {
    $event->userData['startTime'] = microtime(true);
    error_log("Получено событие: " . get_class($event));
});

// Обработка кнопки start
$bot->onBotStarted(function (BotStartedEvent $event): bool {
    $user = $event->getUser();
    
    $message = "Привет, {$user->getFirstName()}!\n\n"
             . "Я тестовый бот. Отправьте мне сообщение.";
    
    $event->sendToUser($message);

    return true;
});

// Обработка сообщений
$bot->onMessageCreated(function (MessageCreatedEvent $event): bool {
    $text = $event->getMessage()->getText();
    
    // Эхо-бот: повторяем сообщение
    $event->reply('Вы: ' . $text);

    return true;
});

// Обработка исключений
$bot->onException(function (Throwable $exception, BaseEvent $event): bool {
    error_log("Ошибка при обработке события: " . $exception->getMessage());

    return true; // Отмечаем как обработанное, чтобы не прерывать работу
});

// Финальная обработка — статистика
$bot->onFinal(function (BaseEvent $event): void {
    if (isset($event->userData['startTime'])) {
        $duration = microtime(true) - $event->userData['startTime'];
        
        error_log(sprintf('Событие %s обработано за %.3f сек', $event::class, $duration));
    }
});

// Запуск обработки обновлений
$bot->handleFromGlobal();
```
