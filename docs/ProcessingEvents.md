# Обработка событий бота

## Введение

**События бота** формально относятся к боту `MaxBot`, но на самом деле, никаким образом от него не зависят и могут
существовать сами по себе.

**События бота** создаются ботом в процессе [обработки событий](ProcessingUpdates.md). Каждое событие с сервера
преобразуется в объект соответствующего класса **события бота**, который передаётся в обработчики.

Об обработке **команд** читайте в разделе [Обработка команд](ProcessingCommands.md).

Об обработке **нажатий кнопок** читайте в разделе [Обработка нажатий кнопок](ProcessingCallbacks.md).

**Связанная документация:**

- [Примеры обработки обновлений](Examples/ProcessingUpdates.md)
- [Примеры обработки событий бота](Examples/ProcessingEvents.md)
- [Примеры обработки команд](Examples/ProcessingCommands.md)
- [Примеры обработки нажатий кнопок](Examples/ProcessingCallbacks.md)

## Типы событий

Каждому типу события из API Max соответствует свой класс события бота:

| Класс события                     | Описание                           |
|-----------------------------------|------------------------------------|
| `BotAddedToChatEvent`             | Бот добавлен в чат или канал       |
| `BotAdminPermissionsChangedEvent` | Изменены права бота-администратора |
| `BotRemovedFromChatEvent`         | Бот удалён из чата или канала      |
| `BotStartedEvent`                 | Начат диалог с ботом               |
| `BotStoppedEvent`                 | Диалог с ботом приостановлен       |
| `ChatTitleChangedEvent`           | Изменён заголовок чата             |
| `CommentCreatedEvent`             | Новый комментарий к посту в канале |
| `CommentEditedEvent`              | Комментарий отредактирован         |
| `CommentRemovedEvent`             | Комментарий удалён                 |
| `DialogClearedEvent`              | Диалог очищен                      |
| `DialogMutedEvent`                | Уведомления выключены              |
| `DialogRemovedEvent`              | Диалог удалён                      |
| `DialogUnmutedEvent`              | Уведомления включены               |
| `MessageCallbackEvent`            | Нажата callback-кнопка в сообщении |
| `MessageCreatedEvent`             | Новое сообщение в чате             |
| `MessageEditedEvent`              | Сообщение отредактировано          |
| `MessageRemovedEvent`             | Сообщение удалено                  |
| `UnknownEvent`                    | Неизвестное событие                |
| `UserAddedToChatEvent`            | Пользователь добавлен в чат        |
| `UserRemovedFromChatEvent`        | Пользователь удалён из чата        |

Если у Вас по одному обработчику на каждый тип **события**, у Вас нет необходимости разбираться в **статусах событий**.
В противном случае обязательно ознакомьтесь с разделом [«Статус события»](#статус-события).

## Добавление обработчиков

### Регистрация методов объекта через атрибуты

Для каждого метода `on*` класса `MaxBot` есть одноимённый атрибут с заглавной буквы
в пространстве имён `MaxMessenger\Bot\MaxBot\Attribute`: `On`, `OnMessageCreated`, `OnCommand`,
`OnCallback`, `OnCallbackJson`, `OnPrepare`, `OnException` и остальные.

Передайте готовый объект в `registerHandlers()`, чтобы зарегистрировать его публичные методы,
отмеченные атрибутами. Унаследованные публичные методы также регистрируются; непубличные методы пропускаются.
Один метод может иметь несколько атрибутов, включая повторяющиеся.

```php
use MaxMessenger\Bot\MaxBot\Attribute\OnCommand;
use MaxMessenger\Bot\MaxBot\Attribute\OnMessageCreated;
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;

$handlers = new class {
    #[OnCommand('start')]
    #[OnCommand('help')]
    public function command(MessageCreatedEvent $event, string $command, ?string $payload): void
    {
        $event->sendMessageToUser('Команда: ' . $command);
    }

    #[OnMessageCreated]
    public function message(MessageCreatedEvent $event): void
    {
        $event->sendMessageToUser('Сообщение получено');
    }
};

$bot->registerHandlers($handlers);
```

Параметры атрибутов повторяют параметры соответствующих `on*` методов без `$handler`:
`#[On(UpdateType::MessageCreated)]`, `#[OnCallback('like')]`,
`#[OnCallbackJson('save')]`. Аргументы и возвращаемое значение метода обрабатываются
так же, как при регистрации замыкания через соответствующий `on*` метод.

### on — обработчик типа обновления

Регистрирует обработчик для определённого типа обновления.

```php
use MaxMessenger\Bot\MaxBot;
use MaxMessenger\Bot\Model\Enum\UpdateType;
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;

$bot = new MaxBot('your-access-token', 'your-secret', useRussianTrustedCaCertificates: true);

$bot->on(UpdateType::MessageCreated, function (MessageCreatedEvent $event): void {
    // Обработка нового сообщения
});
```

### Специфичные обработчики событий

Вы можете использовать удобные методы для регистрации обработчиков конкретных типов событий:

```php
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageEditedEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageCallbackEvent;

// Обработка новых сообщений
$bot->onMessageCreated(function (MessageCreatedEvent $event): void {
    // Обработка нового сообщения
});

// Обработка отредактированных сообщений
$bot->onMessageEdited(function (MessageEditedEvent $event): void {
    // Обработка отредактированного сообщения
});

// Обработка нажатий кнопок
$bot->onMessageCallback(function (MessageCallbackEvent $event): void {
    // Обработка нажатия кнопки
});
```

У `onMessageCreated()` параметр `prepend: true` добавляет обработчик в начало очереди создания сообщений.
По умолчанию обработчик добавляется в конец. Для регистрации через атрибут используйте
`#[OnMessageCreated(prepend: true)]`.

Доступные методы: `onMessageCreated`, `onMessageEdited`, `onMessageRemoved`, `onMessageCallback`,
`onCommentCreated`, `onCommentEdited`, `onCommentRemoved`, `onBotStarted`, `onBotStopped`, `onBotAddedToChat`,
`onBotRemovedFromChat`, `onBotAdminPermissionsChanged`, `onChatTitleChanged`, `onDialogCleared`, `onDialogMuted`,
`onDialogUnmuted`, `onDialogRemoved`, `onUserAddedToChat`, `onUserRemovedFromChat`, `onUnknown`.

### onFallback — обработчик не обработанных событий

Регистрирует обработчик, который вызывается только если событие не было обработано другими списками.

```php
$bot->onFallback(function (BaseEvent $event): void {
    // Обработка события, которое не обработано другими обработчиками
});
```

### onPrepare — предварительный обработчик

Регистрирует предварительный обработчик, вызываемый для всех событий первым.

```php
$bot->onPrepare(function (BaseEvent $event): void {
    // Предварительная обработка всех событий
    // Можно записать данные в $event->userData
    $event->userData['processedAt'] = time();
});
```

Предварительный обработчик, как и основные обработчики, может отметить событие как обработанное. Это может быть
использовано, например, для временного отключения обработки событий во время технических работ или для глобальной
фильтрации событий.

```php
$maintenanceMode = true; // Флаг режима обслуживания

$bot->onPrepare(function (BaseEvent $event) use ($maintenanceMode): bool {
    if ($maintenanceMode) {
        // В режиме обслуживания отмечаем все события как обработанные
        return true;
    }

    return false; // В обычном режиме продолжаем обработку
});
```

### onFinal — финальный обработчик

Регистрирует финальный обработчик, вызываемый для всех событий последним (включая уже обработанные).

```php
$bot->onFinal(function (BaseEvent $event): void {
    // Финальная обработка всех событий
    // Вызывается всегда, независимо от статуса события
});
```

### onException — обработчик исключений

Регистрирует обработчик исключений, возникших при обработке событий.

```php
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use Throwable;

$bot->onException(function (Throwable $exception, BaseEvent $event): bool {
    // Обработка исключения
    // Можно изменить статус события

    return true; // Отмечаем событие как обработанное, исключение не будет выброшено
});
```

Дополнительная документация: [Обработка ошибок в обработчиках событий](HandlingErrorsInEventHandlers.md)

## Статус события

У **события** есть статус, хранящийся в публичном свойстве `$isHandled`.

Статус может иметь 3 состояния:

- `true` — Событие обработано
- `false` — Событие не обработано
- `null` — Событие не обработано

Если обработчик завершился без возвращаемого значения и явно не изменил статус, он определяется параметром
`$defaultIsHandled`: по умолчанию `true`. При обходе списков Prepare и Final метод `MaxBot::handleEvent()`
явно передаёт `null`. Прямой вызов `BaseEvent::handle()` также использует `true`.

Если **событие** отмечается **обработанным**, обработка другими обработчиками не производится.

Внутри обработчика статус **события** можно менять следующими способами:

- Вернуть из обработчика `true` или `false`
- Завершить обработчик без возвращаемого значения: статус по умолчанию зависит от списка
- Напрямую изменить публичное свойство `$event->isHandled`
- Вызвать методы **события** `$event->markAsHandled()` или `$event->markAsUnhandled()`
- Вызвать методы **события** `$event->break()` или `$event->continue()`
- Вызвать статический метод `Event::break()` или `Event::continue()`

## Прерывание обработки события

Прервать обработку события можно следующими способами:

- Используя `return` в обработчике
    - Если при выходе из обработчика вернуть `true` или `false`, это значение будет сохранено в статус **события**
    - Завершение без возвращаемого значения по умолчанию отмечает событие обработанным в обычных списках;
      в Prepare и Final статус остаётся `null` и обход списка продолжается
- Используя методы `BaseEvent`
    - `$event->break()` — Завершит обработку **события** и установит статус **события** в `true`
    - `$event->continue()` — Завершит обработку **события** и установит статус **события** в `false`
    - `$event->exit()` — Завершит обработку **события** не меняя статус **события**
- Используя класс обёртку для служебных исключений `EventException`
    - `Event::break()` — Завершит обработку **события** и установит статус **события** в `true`
    - `Event::continue()` — Завершит обработку **события** и установит статус **события** в `false`
    - `Event::exit()` — Завершит обработку **события** не меняя статус **события**

## Порядок обработки списков

В классе `MaxBot` есть **списки** обработчиков **событий**:

- **Prepare Handlers** — Список предварительных обработчиков
- **Event Handlers** — Обработчики, сгруппированные по классу **события**
- **Typed Handlers** — Обработчики, сгруппированные по типу **обновления**
- **Fallback Handlers** — Список обработчиков для **событий**, которые не были обработаны другими списками
- **Final Handlers** — Список финальных обработчиков
- **Exception Handlers** — Обработчики исключений, возникших при обработке **событий**

Порядок обработки списков:

1. Для всех **событий** запускаются обработчики из списка **Prepare Handlers**
2. Для соответствующих классов **событий** запускаются обработчики из списка **Event Handlers**
3. Для соответствующих типов **обновлений** запускаются обработчики из списка **Typed Handlers**
4. Для всех **событий** запускаются обработчики из списка **Fallback Handlers**
5. Для всех **событий** (*включая уже обработанные*) запускаются обработчики из списка **Final Handlers**

### Prepare Handlers

Обработчики предварительной обработки событий.

Тут Вы можете обработать все входящие события, произвести предварительную обработку или проверку. На всех этапах
обработки Вам доступен массив вспомогательных данных события `$event->userData`. Вы можете сохранять там данные, которые
будут доступны другим обработчикам этого события.

### Event Handlers

Обработчики событий, разделённые по классам событий.

Получая события в этом списке, Вы всегда будете точно знать, какой класс у обрабатываемого события.

### Typed Handlers

Обработчики событий, разделённые по типу обновления.

### Fallback Handlers

Обработчики всех ранее не обработанных событий.

### Final Handlers

Финальные обработчики.

Вызываются всегда, независимо от того, было обработано событие или нет.

### Exception Handlers

Если в процессе обработки **событий** возникло необработанное исключение, запускаются обработчики из списка
`Exception Handlers`.

> Все обработчики списка будут запущены независимо от статуса события.

Статус события можно менять ранее описанными способами.

- Если ни один обработчик исключений не поменял статус **события** на `true` или `false`,
  то исключение будет выброшено дальше
- Если хоть один обработчик исключений поменял статус на `true`, **событие** будет отмечено обработанным
- В остальных случаях обработка **события** будет продолжена другими обработчиками

## Получение статуса события

В начале обработки каждым обработчиком, `BaseEvent::$isHandled` будет содержать значение,
актуальное на момент генерации исключения.

Вы можете узнать, было ли обработано событие. Для этого Вы должны проверить состояние свойства `BaseEvent::$handledIn`.
Если событие не было обработано, данное свойство будет содержать `null`.

Вы можете узнать, в каком списке обрабатывалось событие, когда возникла ошибка. Для этого Вы должны проверить состояние
свойства `BaseEvent::$currentHandlerListType`.

## Реакция на события

Каждый класс события предоставляет вспомогательные методы для упрощения работы.
Ниже описаны методы для каждого типа события.

### BaseEvent (базовый класс)

Все события наследуют от `BaseEvent` и имеют следующие методы:

| Метод               | Возвращает          | Описание                                                      |
|---------------------|---------------------|---------------------------------------------------------------|
| `break()`           | `never`             | Прервать обработку, установить статус `true`                  |
| `continue()`        | `never`             | Прервать обработку, установить статус `false`                 |
| `exit()`            | `never`             | Прервать обработку, статус не менять                          |
| `getChatId()`       | `int\|null`         | ID чата события (`null`, если чата у события нет)             |
| `getTimestamp()`    | `DateTimeImmutable` | Время, когда произошло событие                                |
| `getTimestampRaw()` | `non-negative-int`  | Время, когда произошло событие (Unix-время в миллисекундах)   |
| `getUser()`         | `User\|null`        | Пользователь события (`null`, если его у события нет)         |
| `getUserId()`       | `int\|null`         | ID пользователя события (`null`, если его у события нет)      |
| `handle($handler)`  | `bool`              | Обработать событие заданным обработчиком                      |
| `markAsHandled()`   | `void`              | Отметить событие как обработанное                             |
| `markAsUnhandled()` | `void`              | Отметить событие как не обработанное                          |
| `requireChatId()`   | `int`               | ID чата события, `ChatIdMissingException` вместо `null`       |
| `requireUser()`     | `User`              | Пользователь события, `UserMissingException` вместо `null`    |
| `requireUserId()`   | `int`               | ID пользователя события, `UserMissingException` вместо `null` |

Методы `getChatId()`, `getUser()` и `getUserId()` объявлены абстрактными: конкретные события уточняют
их тип возврата — в таблицах ниже он указан для каждого события.

**Свойства:**

| Свойство                  | Тип                     | Описание                                       |
|---------------------------|-------------------------|------------------------------------------------|
| `$update`                 | `Update`                | Объект обновления                              |
| `$apiClient`              | `MaxApiClient`          | API-клиент                                     |
| `$userData`               | `ArrayObject`           | Массив для хранения произвольных данных        |
| `$isHandled`              | `bool\|null`            | Статус обработки в рамках текущего обработчика |
| `$handledIn`              | `HandlerListType\|null` | Список, в котором событие было обработано      |
| `$currentHandlerListType` | `HandlerListType\|null` | Текущий обрабатываемый список                  |

### SendMessageToChatTrait (трейд отправки в чат)

Предоставляет методы:

| Метод                                                      | Возвращает          | Описание                                                                                     |
|------------------------------------------------------------|---------------------|----------------------------------------------------------------------------------------------|
| `sendActionToChat($action)`                                | `void`              | Отправить действие бота («набор текста», «отправка фото», отметку о прочтении) в чат события |
| `sendMessageToChat($message, $disableLinkPreview = false)` | `SendMessageResult` | Отправить сообщение в чат события                                                            |

Действие отображается в диалогах и групповых чатах.
Для каналов сервер принимает запрос, но участникам действие не показывается.

**Используют:** `BotAddedToChatEvent`, `BotAdminPermissionsChangedEvent`, `BotStartedEvent`, `ChatTitleChangedEvent`,
`DialogClearedEvent`, `DialogMutedEvent`, `DialogUnmutedEvent`, `MessageCallbackEvent`, `MessageCreatedEvent`,
`MessageEditedEvent`, `MessageRemovedEvent`, `UserAddedToChatEvent`, `UserRemovedFromChatEvent`.

Этих методов нет у событий, после которых бот писать уже не может (`BotStoppedEvent`,
`BotRemovedFromChatEvent`, `DialogRemovedEvent`), у событий комментариев (`CommentCreatedEvent`,
`CommentEditedEvent`, `CommentRemovedEvent`) и у `UnknownEvent`.

### SendMessageToUserTrait (трейд отправки в диалог)

Предоставляет один метод:

| Метод                                                      | Возвращает          | Описание                                     |
|------------------------------------------------------------|---------------------|----------------------------------------------|
| `sendMessageToUser($message, $disableLinkPreview = false)` | `SendMessageResult` | Отправить сообщение в диалог с пользователем |

Если у события нет пользователя, метод выбросит `UserMissingException`.

**Используют:** `BotAddedToChatEvent`, `BotAdminPermissionsChangedEvent`, `BotRemovedFromChatEvent`,
`BotStartedEvent`, `ChatTitleChangedEvent`, `CommentCreatedEvent`, `CommentEditedEvent`, `CommentRemovedEvent`,
`DialogClearedEvent`, `DialogMutedEvent`, `DialogUnmutedEvent`, `MessageCallbackEvent`, `MessageCreatedEvent`,
`MessageEditedEvent`, `MessageRemovedEvent`, `UserAddedToChatEvent`, `UserRemovedFromChatEvent`.

### MessageEventTrait (трейд сообщения)

Этот трейд используют события, связанные с сообщениями.
Предоставляет методы:

| Метод                                                                             | Возвращает          | Описание                                                         |
|-----------------------------------------------------------------------------------|---------------------|------------------------------------------------------------------|
| `deleteMessage()`                                                                 | `void`              | Удалить сообщение                                                |
| `forwardToChat($chatId)`                                                          | `SendMessageResult` | Переслать сообщение в указанный чат                              |
| `forwardToUser($userId)`                                                          | `SendMessageResult` | Переслать сообщение в диалог с пользователем                     |
| `getChatId()`                                                                     | `int`               | ID диалога, чата или канала, где было отправлено сообщение       |
| `getMessage()`                                                                    | `Message`           | Объект сообщения                                                 |
| `getUser()`                                                                       | `User\|null`        | Отправитель сообщения (может быть `null`)                        |
| `getUserId()`                                                                     | `int\|null`         | ID отправителя сообщения (может быть `null`)                     |
| `isChannel()`                                                                     | `bool`              | `true`, если сообщение из канала                                 |
| `isChat()`                                                                        | `bool`              | `true`, если сообщение из группы                                 |
| `isDialog()`                                                                      | `bool`              | `true`, если сообщение из диалога                                |
| `isUnsupported()`                                                                 | `bool`              | `true`, если тип чата сообщения неизвестен                       |
| `reply($message, $asReply = false, $disableLinkPreview = false)`                  | `SendMessageResult` | Ответить в чате сообщения                                        |
| `replyToUser($message, $forwardOrigMessage = false, $disableLinkPreview = false)` | `SendMessageResult` | Ответить пользователю в диалоге                                  |
| `requireUser()`                                                                   | `User`              | Отправитель сообщения, `SenderUnknownException` вместо `null`    |
| `requireUserId()`                                                                 | `int`               | ID отправителя сообщения, `SenderUnknownException` вместо `null` |

Дополнительно включает `SendMessageToChatTrait` и `SendMessageToUserTrait` — их методы `sendMessageToChat()`,
`sendActionToChat()` и `sendMessageToUser()` доступны во всех событиях, использующих этот трейд.

**Используют:** `MessageCallbackEvent`, `MessageCreatedEvent`, `MessageEditedEvent`.

### CommentEventTrait (трейд комментария)

Этот трейд используют события, связанные с комментариями к постам в каналах.
Предоставляет методы:

| Метод                               | Возвращает          | Описание                                                           |
|-------------------------------------|---------------------|--------------------------------------------------------------------|
| `deleteComment()`                   | `void`              | Удалить комментарий                                                |
| `editComment($comment)`             | `void`              | Редактировать комментарий                                          |
| `getChatId()`                       | `int`               | ID канала, где был оставлен комментарий                            |
| `getComment()`                      | `CommentMessage`    | Объект комментария                                                 |
| `getPostId()`                       | `non-empty-string`  | ID поста, к которому оставлен комментарий                          |
| `getUser()`                         | `User\|null`        | Отправитель комментария (`null`, если опубликован от имени канала) |
| `getUserId()`                       | `int\|null`         | ID отправителя комментария (может быть `null`)                     |
| `isChannel()`                       | `bool`              | `true`, если комментарий опубликован от имени канала               |
| `isChat()`                          | `bool`              | `true`, если комментарий опубликован пользователем или ботом       |
| `reply($comment, $asReply = false)` | `SendCommentResult` | Ответить комментарием к тому же посту                              |
| `replyToUser($message)`             | `SendMessageResult` | Ответить автору комментария в диалоге                              |
| `requireUser()`                     | `User`              | Отправитель комментария, `SenderUnknownException` вместо `null`    |
| `requireUserId()`                   | `int`               | ID отправителя комментария, `SenderUnknownException` вместо `null` |

Дополнительно включает `SendMessageToUserTrait` — его метод `sendMessageToUser()` доступен во всех
событиях, использующих этот трейд.

**Используют:** `CommentCreatedEvent`, `CommentEditedEvent`.

### MessageCreatedEvent

| Метод                    | Возвращает                | Описание                                                                             |
|--------------------------|---------------------------|--------------------------------------------------------------------------------------|
| `getContactAttachment()` | `ContactAttachment\|null` | Контакт, если он является единственным вложением сообщения                           |
| `getMessage()`           | `Message`                 | Новое созданное сообщение                                                            |
| `getUserLocale()`        | `string\|null`            | Язык пользователя (IETF BCP 47), только в диалогах                                   |
| `isContact()`            | `bool`                    | `true`, если единственное вложение сообщения — контакт                               |
| `isSelfContact()`        | `bool`                    | Проверяет, что пользователь поделился контактом с номером, привязанным к его профилю |

**Трейды:** `MessageEventTrait` (включает `SendMessageToChatTrait` и `SendMessageToUserTrait`)

#### Работа с контактами

В `MessageCreatedEvent` метод `isContact()` показывает, содержит ли сообщение контакт.
Метод `getContactAttachment()` возвращает `ContactAttachment` или `null`, если контакта нет.
Оба метода распознают контакт, когда он является единственным вложением сообщения.

Чтобы проверить, что пользователь прислал номер, привязанный к его профилю МАКС, вызовите `isSelfContact()`.
Метод проверяет подпись контакта; наличие контакта само по себе не подтверждает принадлежность номера отправителю.
Номера из контакта доступны через `$event->getContactAttachment()->getPayload()->getPhones()` после проверки,
что `getContactAttachment()` вернул объект.
См. [пример обработки контакта](Examples/ProcessingEvents.md#обработка-контакта-пользователя).

### MessageEditedEvent

| Метод          | Возвращает | Описание                    |
|----------------|------------|-----------------------------|
| `getMessage()` | `Message`  | Отредактированное сообщение |

**Трейды:** `MessageEventTrait` (включает `SendMessageToChatTrait` и `SendMessageToUserTrait`)

### MessageRemovedEvent

| Метод            | Возвращает         | Описание                                                |
|------------------|--------------------|---------------------------------------------------------|
| `getChatId()`    | `int`              | ID чата, где сообщение было удалено                     |
| `getMessageId()` | `non-empty-string` | ID удалённого сообщения                                 |
| `getUser()`      | `null`             | Всегда `null`: событие не содержит объекта пользователя |
| `getUserId()`    | `int`              | ID пользователя, удалившего сообщение                   |

**Трейды:** `SendMessageToChatTrait`, `SendMessageToUserTrait`

### CommentCreatedEvent

| Метод          | Возвращает       | Описание                    |
|----------------|------------------|-----------------------------|
| `getComment()` | `CommentMessage` | Новый созданный комментарий |

**Трейды:** `CommentEventTrait` (включает `SendMessageToUserTrait`)

### CommentEditedEvent

| Метод          | Возвращает       | Описание                      |
|----------------|------------------|-------------------------------|
| `getComment()` | `CommentMessage` | Отредактированный комментарий |

**Трейды:** `CommentEventTrait` (включает `SendMessageToUserTrait`)

### CommentRemovedEvent

| Метод             | Возвращает               | Описание                                |
|-------------------|--------------------------|-----------------------------------------|
| `getChatId()`     | `int`                    | ID чата, где комментарий был удалён     |
| `getMessageId()`  | `non-empty-string`       | ID удалённого комментария               |
| `getPostId()`     | `non-empty-string\|null` | ID поста в канале, если он передан      |
| `getUser()`       | `null`                   | Всегда `null`                           |
| `getUserId()`     | `int`                    | ID пользователя, удалившего комментарий |
| `reply($comment)` | `SendCommentResult`      | Ответить комментарием к тому же посту   |

**Трейды:** `SendMessageToUserTrait`

Если ID поста не передан, `getPostId()` возвращает `null`, а `reply()` выбрасывает `PostIdMissingException`
до отправки запроса к API.

### MessageCallbackEvent

| Метод                                                                             | Возвращает          | Описание                                     |
|-----------------------------------------------------------------------------------|---------------------|----------------------------------------------|
| `answer($message, $disableLinkPreview = false)`                                   | `void`              | Ответить на callback с обновлением сообщения |
| `deleteMessage()`                                                                 | `void`              | Удалить сообщение                            |
| `forwardToChat($chatId)`                                                          | `SendMessageResult` | Переслать сообщение в указанный чат          |
| `forwardToUser($userId)`                                                          | `SendMessageResult` | Переслать сообщение в диалог с пользователем |
| `getCallback()`                                                                   | `Callback`          | Данные нажатия callback-кнопки               |
| `getChatId()`                                                                     | `int`               | ID чата                                      |
| `getMessage()`                                                                    | `Message`           | Сообщение с нажатой кнопкой                  |
| `getUser()`                                                                       | `User`              | Пользователь, нажавший кнопку                |
| `getUserId()`                                                                     | `int`               | ID пользователя, нажавшего кнопку            |
| `getUserLocale()`                                                                 | `string\|null`      | Язык пользователя (IETF BCP 47)              |
| `isChannel()`                                                                     | `bool`              | `true`, если сообщение из канала             |
| `isChat()`                                                                        | `bool`              | `true`, если сообщение из группы             |
| `isDialog()`                                                                      | `bool`              | `true`, если сообщение из диалога            |
| `reply($message, $asReply = false, $disableLinkPreview = false)`                  | `SendMessageResult` | Ответить в чате сообщения                    |
| `replyToUser($message, $forwardOrigMessage = false, $disableLinkPreview = false)` | `SendMessageResult` | Ответить пользователю в диалоге              |

**Трейды:** `MessageEventTrait` (включает `SendMessageToChatTrait` и `SendMessageToUserTrait`)

### BotStartedEvent

| Метод             | Возвращает               | Описание                              |
|-------------------|--------------------------|---------------------------------------|
| `getChatId()`     | `int`                    | ID диалога                            |
| `getPayload()`    | `non-empty-string\|null` | Данные из дип-линка (до 128 символов) |
| `getUser()`       | `User`                   | Пользователь, запустивший бота        |
| `getUserId()`     | `int`                    | ID пользователя                       |
| `getUserLocale()` | `string\|null`           | Язык пользователя (IETF BCP 47)       |

**Трейды:** `SendMessageToChatTrait`, `SendMessageToUserTrait`

### BotStoppedEvent

| Метод             | Возвращает     | Описание                            |
|-------------------|----------------|-------------------------------------|
| `getChatId()`     | `int`          | ID диалога                          |
| `getUser()`       | `User`         | Пользователь, остановивший бота     |
| `getUserId()`     | `int`          | ID пользователя, остановившего бота |
| `getUserLocale()` | `string\|null` | Язык пользователя (IETF BCP 47)     |

### BotAddedToChatEvent

| Метод         | Возвращает   | Описание                                        |
|---------------|--------------|-------------------------------------------------|
| `getChatId()` | `int`        | ID чата, куда добавлен бот                      |
| `getUser()`   | `User\|null` | Пользователь, добавивший бота, если передан     |
| `getUserId()` | `int\|null`  | ID пользователя, добавившего бота, если передан |
| `isChannel()` | `bool`       | `true`, если бот добавлен в канал               |

**Трейды:** `SendMessageToChatTrait`, `SendMessageToUserTrait`

### BotRemovedFromChatEvent

| Метод         | Возвращает   | Описание                                       |
|---------------|--------------|------------------------------------------------|
| `getChatId()` | `int`        | ID чата, откуда удалён бот                     |
| `getUser()`   | `User\|null` | Пользователь, удаливший бота, если передан     |
| `getUserId()` | `int\|null`  | ID пользователя, удалившего бота, если передан |
| `isChannel()` | `bool`       | `true`, если бот удалён из канала              |

**Трейды:** `SendMessageToUserTrait`

> Отправки в чат у этого события нет: бот из чата уже удалён.

### BotAdminPermissionsChangedEvent

| Метод                 | Возвращает                        | Описание                                                |
|-----------------------|-----------------------------------|---------------------------------------------------------|
| `getBotId()`          | `int`                             | ID бота, чьи права администратора были изменены         |
| `getChatId()`         | `int`                             | ID группового чата или канала, где произошло событие    |
| `getPermissions()`    | `list<ChatAdminPermission>\|null` | Права администратора                                    |
| `getPermissionsRaw()` | `list<string>\|null`              | Права администратора в виде строк                       |
| `getUser()`           | `null`                            | Всегда `null`: событие не содержит объекта пользователя |
| `getUserId()`         | `int`                             | ID пользователя или бота, изменившего права             |
| `isAdmin()`           | `bool`                            | Является ли бот администратором чата или канала         |
| `isChannel()`         | `bool`                            | `true`, если событие произошло в канале                 |

**Трейды:** `SendMessageToChatTrait`, `SendMessageToUserTrait`

### ChatTitleChangedEvent

| Метод         | Возвращает         | Описание                              |
|---------------|--------------------|---------------------------------------|
| `getChatId()` | `int`              | ID чата                               |
| `getTitle()`  | `non-empty-string` | Новое название чата                   |
| `getUser()`   | `User`             | Пользователь, изменивший название     |
| `getUserId()` | `int`              | ID пользователя, изменившего название |

**Трейды:** `SendMessageToChatTrait`, `SendMessageToUserTrait`

### DialogClearedEvent

| Метод             | Возвращает     | Описание                             |
|-------------------|----------------|--------------------------------------|
| `getChatId()`     | `int`          | ID чата                              |
| `getUser()`       | `User`         | Пользователь, очистивший историю     |
| `getUserId()`     | `int`          | ID пользователя, очистившего историю |
| `getUserLocale()` | `string\|null` | Язык пользователя (IETF BCP 47)      |

**Трейды:** `SendMessageToChatTrait`, `SendMessageToUserTrait`

### DialogMutedEvent

| Метод                | Возвращает          | Описание                                                              |
|----------------------|---------------------|-----------------------------------------------------------------------|
| `getChatId()`        | `int`               | ID диалога, чата или канала                                           |
| `getMutedUntil()`    | `DateTimeImmutable` | Время, до которого отключены уведомления                              |
| `getMutedUntilRaw()` | `non-negative-int`  | Время, до которого отключены уведомления (Unix-время в миллисекундах) |
| `getUser()`          | `User`              | Пользователь, отключивший уведомления                                 |
| `getUserId()`        | `int`               | ID пользователя, отключившего уведомления                             |
| `getUserLocale()`    | `string\|null`      | Язык пользователя (IETF BCP 47)                                       |

**Трейды:** `SendMessageToChatTrait`, `SendMessageToUserTrait`

### DialogUnmutedEvent

| Метод             | Возвращает     | Описание                                 |
|-------------------|----------------|------------------------------------------|
| `getChatId()`     | `int`          | ID диалога, чата или канала              |
| `getUser()`       | `User`         | Пользователь, включивший уведомления     |
| `getUserId()`     | `int`          | ID пользователя, включившего уведомления |
| `getUserLocale()` | `string\|null` | Язык пользователя (IETF BCP 47)          |

**Трейды:** `SendMessageToChatTrait`, `SendMessageToUserTrait`

### DialogRemovedEvent

| Метод             | Возвращает     | Описание                        |
|-------------------|----------------|---------------------------------|
| `getChatId()`     | `int`          | ID чата                         |
| `getUser()`       | `User`         | Пользователь, удаливший чат     |
| `getUserId()`     | `int`          | ID пользователя, удалившего чат |
| `getUserLocale()` | `string\|null` | Язык пользователя (IETF BCP 47) |

### UserAddedToChatEvent

| Метод            | Возвращает  | Описание                                                |
|------------------|-------------|---------------------------------------------------------|
| `getChatId()`    | `int`       | ID чата                                                 |
| `getInviterId()` | `int\|null` | Пользователь, добавивший в чат (`null`, если по ссылке) |
| `getUser()`      | `User`      | Пользователь, добавленный в чат                         |
| `getUserId()`    | `int`       | ID пользователя, добавленного в чат                     |
| `isChannel()`    | `bool`      | `true`, если пользователь добавлен в канал              |

**Трейды:** `SendMessageToChatTrait`, `SendMessageToUserTrait`

### UserRemovedFromChatEvent

| Метод          | Возвращает  | Описание                                                         |
|----------------|-------------|------------------------------------------------------------------|
| `getAdminId()` | `int\|null` | Администратор, удаливший пользователя (`null`, если покинул сам) |
| `getChatId()`  | `int`       | ID чата                                                          |
| `getUser()`    | `User`      | Пользователь, удалённый из чата                                  |
| `getUserId()`  | `int`       | ID пользователя, удалённого из чата                              |
| `isChannel()`  | `bool`      | `true`, если пользователь удалён из канала                       |

**Трейды:** `SendMessageToChatTrait`, `SendMessageToUserTrait`

### UnknownEvent

Событие неизвестного типа. Дополнительных методов нет, а `getChatId()`, `getUser()` и `getUserId()`
всегда возвращают `null`.

## Вспомогательные данные события

Каждое событие имеет публичное свойство `$userData` — массив произвольных данных, который Вы можете использовать для
передачи информации между обработчиками.

```php
$bot->onPrepare(function (BaseEvent $event): void {
    // Сохраняем время получения события
    $event->userData['receivedAt'] = time();
});

$bot->on(UpdateType::MessageCreated, function (MessageCreatedEvent $event): void {
    // Получаем данные, сохранённые в onPrepare
    $receivedAt = $event->userData['receivedAt'] ?? null;
});
```

Данные из `$userData` сохраняются на протяжении всей цепочки обработки одного события.
