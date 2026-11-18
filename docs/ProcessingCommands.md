# Обработка команд

## Введение

Команды — это сообщения, начинающиеся с символа `/`.
Обработчик получает объект `MessageCreatedEvent`, имя команды без `/` и payload.

Для обработки команд используется класс [CommandHandler](../src/MaxBot/CommandHandler.php), а также методы `MaxBot`:

- `onMessageCreated()` — регистрация обработчика события создания сообщения;
- `getCommandHandler()` — получение основного обработчика команд бота;
- `onCommand()` и `onCommands()` — регистрация отдельной команды или обработчика оставшихся команд.

Связанная документация:

- [Примеры обработки команд](Examples/ProcessingCommands.md)
- [Примеры обработки событий бота](Examples/ProcessingEvents.md)
- [Обработка событий бота](ProcessingEvents.md)
- [API-клиент](ApiClient.md)

## Получение обработчиков

Метод `getCommandHandler()` возвращает основной обработчик `CommandHandler`.
При первом вызове он создаёт и регистрирует его через `onMessageCreated()` в начале очереди,
сохраняя экземпляр для последующих вызовов `getCommandHandler()`, `onCommand()` и `onCommands()`.

Настройки основного обработчика изменяются через его сеттеры.

### CommandHandler

`CommandHandler` обрабатывает команды.
Разделитель имени команды и payload можно настроить через `setCommandSeparator()`.
Команды всегда обрабатываются в личных диалогах с ботом и групповых чатах.
Обработка команд из каналов по умолчанию отключена.

```php
use MaxMessenger\Bot\MaxBot;

$bot = new MaxBot('your-access-token', 'your-secret', useRussianTrustedCaCertificates: true);

$commandHandler = $bot->getCommandHandler()->setCommandSeparator(' ');
```

Чтобы обрабатывать команды также из каналов, включите `allowChannels`:

```php
$commandHandler->setAllowChannels(true);
```

Отдельный обработчик создайте и зарегистрируйте через `onMessageCreated()`:

```php
use MaxMessenger\Bot\MaxBot\CommandHandler;

$commandHandler = new CommandHandler(commandSeparator: ' ', allowChannels: true);
$bot->onMessageCreated($commandHandler->handle(...), prepend: true);
```

## Регистрация обработчиков

Методы `MaxBot::onCommand()` и `MaxBot::onCommands()` используют текущий обработчик команд
и возвращают `MaxBot`. Одноимённые методы `CommandHandler` регистрируют команды напрямую
и возвращают объект обработчика.

| Метод                          | Параметры                                      | Возвращает |
|--------------------------------|------------------------------------------------|------------|
| `MaxBot::onCommand()`          | `$name: non-empty-string`, `$handler: Closure` | `$this`    |
| `MaxBot::onCommands()`         | `$handler: Closure`                            | `$this`    |
| `CommandHandler::onCommand()`  | `$name: non-empty-string`, `$handler: Closure` | `$this`    |
| `CommandHandler::onCommands()` | `$handler: Closure`                            | `$this`    |

Имя команды `$name` указывается без символа `/`.

### Отдельные команды

```php
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;

$bot->onCommand('start', static function (MessageCreatedEvent $event, string $command, ?string $payload): void {
    // Для `/start hello`: $command === 'start', $payload === 'hello'.
});
```

Регистрация напрямую через `CommandHandler` использует ту же сигнатуру:

```php
$commandHandler->onCommand('start', static function (
    MessageCreatedEvent $event,
    string $command,
    ?string $payload,
): void {
    // Обработка команды с дополнительными данными.
});
```

### Остальные команды

```php
$bot->onCommands(static function (MessageCreatedEvent $event, string $command, ?string $payload): void {
    $event->reply("Команда /$command не найдена. Отправьте /help");
});
```

Регистрация напрямую через `CommandHandler` использует ту же сигнатуру:

```php
$commandHandler->onCommands(static function (MessageCreatedEvent $event, string $command, ?string $payload): void {
    // Обработка команды, которую не обработали именные обработчики.
});
```

## Аргументы обработчика

Каждый обработчик получает три аргумента в следующем порядке:

| Аргумент   | Тип                   | Описание                      |
|------------|-----------------------|-------------------------------|
| `$event`   | `MessageCreatedEvent` | Событие создания сообщения    |
| `$command` | `string`              | Имя команды без символа `/`   |
| `$payload` | `string\|null`        | Дополнительные данные команды |

Payload содержит текст после разделителя. Если разделителя нет в настройках или
в тексте команды, payload равен `null`. Пустая строка после разделителя передаётся как `''`.

### Доступ через `userData`

Для доступа к тем же значениям из других обработчиков события они также записываются в `userData`:

| Ключ                            | Тип            | Описание                    |
|---------------------------------|----------------|-----------------------------|
| `$event->userData['__command']` | `string`       | Имя команды без символа `/` |
| `$event->userData['__payload']` | `string\|null` | Payload команды             |

## Настройка обработчиков

Параметры существующего обработчика изменяются его методами `set*()`. Методы `get*()` возвращают текущие настройки.

### CommandHandler

| Метод                   | Параметры                                   | Возвращает               |
|-------------------------|---------------------------------------------|--------------------------|
| `getCommandSeparator()` | —                                           | `non-empty-string\|null` |
| `setCommandSeparator()` | `$commandSeparator: non-empty-string\|null` | `$this`                  |
| `getAllowChannels()`    | —                                           | `bool`                   |
| `setAllowChannels()`    | `$allowChannels: bool`                      | `$this`                  |

```php
$commandHandler->setCommandSeparator(' ');

// Для сообщения "/start arg1 arg2" обработчик получит:
// $command === 'start', $payload === 'arg1 arg2'.
```

## Особенности обработки

### Порядок обработки

Обработчик команд регистрируется перед обработчиками новых сообщений. Внутри `CommandHandler` сначала вызываются
обработчики конкретной команды из `onCommand()`, затем, если команда не обработана, — обработчики оставшихся команд
из `onCommands()`. В каждой группе обработчики вызываются в порядке регистрации.

Для отдельного `CommandHandler` используйте `onMessageCreated($commandHandler->handle(...), prepend: true)`,
чтобы добавить его перед уже зарегистрированными обработчиками новых сообщений, включая другие обработчики команд.

### Статус события

Обработчик может вернуть `true`, чтобы отметить событие обработанным и остановить дальнейший обход,
или `false`, чтобы продолжить обработку. При завершении без `return` событие по умолчанию считается обработанным.
Если обработчик сам изменил статус через методы события, используется установленное им значение.

Подробнее — в [обработке событий бота](ProcessingEvents.md) и [обработке ошибок](HandlingErrorsInEventHandlers.md).

### Ограничения

- Разделитель команды по умолчанию не задан.
- Команды из каналов (`ChatType::Channel`) игнорируются по умолчанию; для их обработки задайте `allowChannels: true`.
- Команда должна начинаться с символа `/`.

## Публикация списка команд бота

Чтобы команды отображались в диалогах и чатах с ботом, передайте их на сервер через `MaxApiClient::editMyCommands()`.

Подсказки при вводе `/` доступны в диалогах на iOS, Android и десктопе, а в групповых чатах — на iOS и Android.
На iOS в диалоге список также открывается кнопкой `/`. В каналах команды не отображаются.

```php
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Model\Request\BotCommand;
use MaxMessenger\Bot\Model\Request\BotCommandsPatch;

$apiClient = new MaxApiClient('your-access-token', useRussianTrustedCaCertificates: true);

$apiClient->editMyCommands(BotCommandsPatch::make([
    new BotCommand('start', 'Начать работу с ботом'),
    new BotCommand('help', 'Получить справку'),
]));
```

Метод заменяет весь список команд. Для удаления всех команд передайте пустой список: `BotCommandsPatch::make([])`.
