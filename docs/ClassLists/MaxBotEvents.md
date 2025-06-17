# События MaxBot

## Список классов событий

- [**BaseEvent**](../../src/MaxBot/Events/BaseEvent.php) — Базовый класс для всех событий.
- [**BotAddedToChatEvent**](../../src/MaxBot/Events/BotAddedToChatEvent.php) — Событие для
  модели [BotAddedToChatUpdate](../../src/Models/Responses/BotAddedToChatUpdate.php).
- [**BotRemovedFromChatEvent**](../../src/MaxBot/Events/BotRemovedFromChatEvent.php) — Событие для
  модели [BotRemovedFromChatUpdate](../../src/Models/Responses/BotRemovedFromChatUpdate.php).
- [**BotStartedEvent**](../../src/MaxBot/Events/BotStartedEvent.php) — Событие для
  модели [BotStartedUpdate](../../src/Models/Responses/BotStartedUpdate.php).
- [**BotStoppedEvent**](../../src/MaxBot/Events/BotStoppedEvent.php) — Событие для
  модели [BotStoppedUpdate](../../src/Models/Responses/BotStoppedUpdate.php).
- [**ChatTitleChangedEvent**](../../src/MaxBot/Events/ChatTitleChangedEvent.php) — Событие для
  модели [ChatTitleChangedUpdate](../../src/Models/Responses/ChatTitleChangedUpdate.php).
- [**DialogClearedEvent**](../../src/MaxBot/Events/DialogClearedEvent.php) — Событие для
  модели [DialogClearedUpdate](../../src/Models/Responses/DialogClearedUpdate.php).
- [**DialogMutedEvent**](../../src/MaxBot/Events/DialogMutedEvent.php) — Событие для
  модели [DialogMutedUpdate](../../src/Models/Responses/DialogMutedUpdate.php).
- [**DialogRemovedEvent**](../../src/MaxBot/Events/DialogRemovedEvent.php) — Событие для
  модели [DialogRemovedUpdate](../../src/Models/Responses/DialogRemovedUpdate.php).
- [**DialogUnmutedEvent**](../../src/MaxBot/Events/DialogUnmutedEvent.php) — Событие для
  модели [DialogUnmutedUpdate](../../src/Models/Responses/DialogUnmutedUpdate.php).
- [**Event**](../../src/MaxBot/Events/Event.php) — Вспомогательный класс для управления потоком обработки событий.
- [**MessageCallbackEvent**](../../src/MaxBot/Events/MessageCallbackEvent.php) — Событие для
  модели [MessageCallbackUpdate](../../src/Models/Responses/MessageCallbackUpdate.php).
- [**MessageCreatedEvent**](../../src/MaxBot/Events/MessageCreatedEvent.php) — Событие для
  модели [MessageCreatedUpdate](../../src/Models/Responses/MessageCreatedUpdate.php).
- [**MessageEditedEvent**](../../src/MaxBot/Events/MessageEditedEvent.php) — Событие для
  модели [MessageEditedUpdate](../../src/Models/Responses/MessageEditedUpdate.php).
- [**MessageEventTrait**](../../src/MaxBot/Events/MessageEventTrait.php) — Трейт для событий, связанных с сообщениями.
- [**MessageRemovedEvent**](../../src/MaxBot/Events/MessageRemovedEvent.php) — Событие для
  модели [MessageRemovedUpdate](../../src/Models/Responses/MessageRemovedUpdate.php).
- [**UnknownEvent**](../../src/MaxBot/Events/UnknownEvent.php) — Событие для неизвестных типов обновлений.
- [**UserAddedToChatEvent**](../../src/MaxBot/Events/UserAddedToChatEvent.php) — Событие для
  модели [UserAddedToChatUpdate](../../src/Models/Responses/UserAddedToChatUpdate.php).
- [**UserEventTrait**](../../src/MaxBot/Events/UserEventTrait.php) — Трейт для событий, связанных с пользователями.
- [**UserRemovedFromChatEvent**](../../src/MaxBot/Events/UserRemovedFromChatEvent.php) — Событие для
  модели [UserRemovedFromChatUpdate](../../src/Models/Responses/UserRemovedFromChatUpdate.php).

### Соглашения

- Формат элемента списка:
    ```markdown
    - [**EventName**](../../src/MaxBot/Events/EventName.php) — Событие для модели [EventUpdate](../../src/Models/Responses/EventUpdate.php).
    ```
- Список должен быть отсортирован по алфавиту
