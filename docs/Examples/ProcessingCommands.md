# Примеры обработки команд

## Введение

В этом документе представлены примеры обработки команд с использованием `CommandHandler`.
Обработчики можно зарегистрировать через методы `MaxBot::onCommand()` и `MaxBot::onCommands()`.
Для настройки основного обработчика используется `MaxBot::getCommandHandler()`.

Связанная документация:

- [Обработка команд](../ProcessingCommands.md)
- [Обработка событий бота](../ProcessingEvents.md)
- [Обработка нажатий кнопок](../ProcessingCallbacks.md)
- [Примеры обработки событий бота](ProcessingEvents.md)
- [Примеры обработки нажатий кнопок](ProcessingCallbacks.md)

## Базовые примеры

### Обработка команды `start`

Пример регистрации обработчика команды `start`:

```php
use MaxMessenger\Bot\MaxBot;
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;

$bot = new MaxBot('your-access-token', 'your-secret', useRussianTrustedCaCertificates: true);

$bot->onCommand('start', static function (MessageCreatedEvent $event, string $command, ?string $payload): void {
    $event->reply('Привет! Я тестовый бот. Отправьте /help для списка команд.');
});
```

### Обработка нескольких команд

```php
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;

// Команда /help
$bot->onCommand('help', static function (MessageCreatedEvent $event, string $command, ?string $payload): void {
    $helpText = "Доступные команды:\n"
        . "/start - Запустить бота\n"
        . "/help - Показать помощь\n"
        . "/info - Информация о боте\n"
        . "/settings - Настройки";

    $event->reply($helpText);
});

// Команда /info
$bot->onCommand('info', static function (MessageCreatedEvent $event, string $command, ?string $payload): void {
    $botInfo = $event->apiClient->getMyInfo();

    $infoText = "Бот: {$botInfo->getFullName()}\n"
        . "ID: {$botInfo->getUserId()}";

    $event->reply($infoText);
});
```

### Обработка неизвестных команд

Пример обработки команд, которые не обработали именные обработчики:

```php
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;

// Обработчик для всех неизвестных команд
$bot->onCommands(static function (MessageCreatedEvent $event, string $command, ?string $payload): void {
    $event->reply("Команда /$command не найдена. Отправьте /help для списка команд.");
});
```

## Работа с аргументами

### Использование разделителя

В примере пробел используется как разделитель имени команды и payload:

```php
use MaxMessenger\Bot\MaxBot;
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;

$bot = new MaxBot('your-access-token', 'your-secret', useRussianTrustedCaCertificates: true);

$bot->getCommandHandler()->setCommandSeparator(' ');

// Команда с аргументами: /echo Привет, мир!
$bot->onCommand('echo', static function (MessageCreatedEvent $event, string $command, ?string $payload): void {
    if ($payload === null) {
        $event->reply('Использование: /echo <текст>');
    } else {
        $event->reply('Вы сказали: ' . $payload);
    }
});
```

### Разбор аргументов

Пример разделения payload и проверки аргументов команды:

```php
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;

$bot->getCommandHandler()->setCommandSeparator(' ');

// Команда: /add 10 20
$bot->onCommand('add', static function (MessageCreatedEvent $event, string $command, ?string $payload): void {
    if ($payload === null) {
        $event->reply('Использование: /add <число1> <число2>');

        return;
    }

    $parts = explode(' ', $payload);

    if (count($parts) !== 2 || !is_numeric($parts[0]) || !is_numeric($parts[1])) {
        $event->reply('Ошибка: укажите два числа через пробел');

        return;
    }

    $result = (float) $parts[0] + (float) $parts[1];
    $event->reply("Результат: {$parts[0]} + {$parts[1]} = $result");
});
```

## Публикация списка команд бота

Список команд, который пользователь видит в меню мессенджера, задаётся через API-клиент:

```php
use MaxMessenger\Bot\Model\Request\BotCommand;
use MaxMessenger\Bot\Model\Request\BotCommandsPatch;

$apiClient = $bot->getApiClient();

$apiClient->editMyCommands(BotCommandsPatch::make([
    new BotCommand('start', 'Начать работу с ботом'),
    new BotCommand('help', 'Показать помощь'),
    new BotCommand('info', 'Информация о боте'),
]));
```

## Отдельный обработчик

В примере отдельный `CommandHandler` зарегистрирован через `onMessageCreated()`:

```php
use MaxMessenger\Bot\MaxBot\CommandHandler;
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;

$commandHandler = new CommandHandler(commandSeparator: ' ');
$bot->onMessageCreated($commandHandler->handle(...), prepend: true);

$commandHandler->onCommand('start', static function (
    MessageCreatedEvent $event,
    string $command,
    ?string $payload,
): void {
    $event->reply('Привет! Отправьте /help для списка команд.');
});
```
