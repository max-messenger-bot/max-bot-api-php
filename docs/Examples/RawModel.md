# Примеры использования RawModel

## Создание RawModel из стандартной модели

Вы можете преобразовать любую стандартную модель запроса в `RawModel`:

```php
use MaxMessenger\Bot\Model\Request\NewMessageBody;

// Создаём стандартную модель
$message = new NewMessageBody('Привет, мир!');

// Преобразуем в RawModel
$rawMessage = $message->getRawModel();

// Теперь можем добавить произвольные поля
$rawMessage['disable_notification'] = true;

// Используем в API-клиенте
$apiClient->sendMessageToChat($chatId, $rawMessage);
```

## Создание RawModel напрямую

Вы можете создать `RawModel` с произвольными данными:

```php
use MaxMessenger\Bot\Model\Request\RawModel;

// Создаём сообщение с нестандартной структурой
$message = new RawModel([
    'text' => 'Привет, мир!',
    'text_format' => 'markdown',
    'disable_notification' => true,
]);

$apiClient->sendMessageToChat($chatId, $message);
```

## Модификация стандартной модели через RawModel

Иногда нужно лишь немного изменить стандартную модель:

```php
use MaxMessenger\Bot\Model\Request\ChatPatch;

// Создаём стандартную модель для редактирования чата
$chatPatch = new ChatPatch(title: 'Новое название');

// Преобразуем в RawModel для добавления нестандартных полей
$rawChatPatch = $chatPatch->getRawModel();
$rawChatPatch['custom_field'] = 'custom_value';

// Применяем изменения
$apiClient->editChat($chatId, $rawChatPatch);
```

## Работа с вложенными моделями

Вложенные модели автоматически преобразуются в `RawModel`:

```php
use MaxMessenger\Bot\Model\Request\NewMessageBody;
use MaxMessenger\Bot\Model\Request\RawModel;

// Создаём сообщение
$message = new NewMessageBody('Текст сообщения');
$rawMessage = $message->getRawModel();

$message = (new NewMessageBody('Текст сообщения'))
    ->setReplyLink('mid.ffffbea82cf265aa15ab6843019d844d');
$rawMessage = $message->getRawModel();

var_dump($rawMessage);
// class MaxMessenger\Bot\Model\Request\RawModel (1) {
//   protected array $data => array(3) {
//     'text' => string(29) "Текст сообщения"
//     'notify' => bool(true)
//     'link' => class MaxMessenger\Bot\Model\Request\NewMessageLink (3) {
//       protected array $data => array(2) { ... }
//     }
//   }
// }

// Вложенные структуры тоже будут RawModel
var_dump($rawMessage['link']);
// class MaxMessenger\Bot\Model\Request\RawModel (1) {
//   protected array $data => array(2) {
//     'mid' => string(36) "mid.ffffbea82cf265aa15ab6843019d844d"
//     'type' => enum MaxMessenger\Bot\Model\Enum\MessageLinkType::Reply : string("reply");
//   }
// }

```

## Наследование от RawModel

Вы можете создать свою собственную модель, унаследовавшись от `RawModel`:

```php
use MaxMessenger\Bot\Model\Request\RawModel;

class CustomMessage extends RawModel
{
    public function __construct(string $text, bool $disableNotification = false)
    {
        parent::__construct([
            'text' => $text,
            'text_format' => 'markdown',
            'disable_notification' => $disableNotification,
        ]);
    }

    public function setReplyMessageId(string $messageId): self
    {
        $this['reply_to'] = ['message_id' => $messageId];
        
        return $this;
    }
}

// Использование
$message = new CustomMessage('Привет, мир!');
$message->setReplyMessageId('mid.abc123');

$apiClient->sendMessageToChat($chatId, $message);
```

## Доступ к данным RawModel

`RawModel` реализует `ArrayAccess`, поэтому вы можете работать с ним как с массивом:

```php
use MaxMessenger\Bot\Model\Request\NewMessageBody;

$message = new NewMessageBody('Текст');
$rawMessage = $message->getRawModel();

// Чтение данных
$text = $rawMessage['text'];

// Изменение данных
$rawMessage['text'] = 'Новый текст';

// Удаление поля
unset($rawMessage['text_format']);

// Проверка существования поля
if (isset($rawMessage['text'])) {
    // Поле существует
}
```

## Пример: отправка сообщения с произвольной разметкой

```php
use MaxMessenger\Bot\Model\Request\RawModel;

$message = new RawModel([
    'text' => '*Жирный текст* и _курсив_',
    'text_format' => 'markdown',
    'link_preview' => false,
]);

$apiClient->sendMessageToChat($chatId, $message);
```

## Пример: подписка с нестандартными параметрами

```php
use MaxMessenger\Bot\Model\Request\RawModel;

$subscription = new RawModel([
    'url' => 'https://your-webhook.example.com/max',
    'update_types' => ['message_created', 'message_callback'],
    'secret' => 'your-secret-key',
]);

$apiClient->subscribe($subscription);
```
