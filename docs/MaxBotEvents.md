# События MaxBot

## Список классов событий

- [**BaseEvent**](../src/MaxBot/Events/BaseEvent.php) — Базовый класс для всех событий.
- [**BotAddedEvent**](../src/MaxBot/Events/BotAddedEvent.php) — Событие для
  модели [BotAddedUpdate](../src/Models/Response/BotAddedUpdate.php).
- [**BotRemovedEvent**](../src/MaxBot/Events/BotRemovedEvent.php) — Событие для
  модели [BotRemovedUpdate](../src/Models/Response/BotRemovedUpdate.php).
- [**BotStartedEvent**](../src/MaxBot/Events/BotStartedEvent.php) — Событие для
  модели [BotStartedUpdate](../src/Models/Response/BotStartedUpdate.php).
- [**BotStoppedEvent**](../src/MaxBot/Events/BotStoppedEvent.php) — Событие для
  модели [BotStoppedUpdate](../src/Models/Response/BotStoppedUpdate.php).
- [**ChatTitleChangedEvent**](../src/MaxBot/Events/ChatTitleChangedEvent.php) — Событие для
  модели [ChatTitleChangedUpdate](../src/Models/Response/ChatTitleChangedUpdate.php).
- [**DialogClearedEvent**](../src/MaxBot/Events/DialogClearedEvent.php) — Событие для
  модели [DialogClearedUpdate](../src/Models/Response/DialogClearedUpdate.php).
- [**DialogMutedEvent**](../src/MaxBot/Events/DialogMutedEvent.php) — Событие для
  модели [DialogMutedUpdate](../src/Models/Response/DialogMutedUpdate.php).
- [**DialogRemovedEvent**](../src/MaxBot/Events/DialogRemovedEvent.php) — Событие для
  модели [DialogRemovedUpdate](../src/Models/Response/DialogRemovedUpdate.php).
- [**DialogUnmutedEvent**](../src/MaxBot/Events/DialogUnmutedEvent.php) — Событие для
  модели [DialogUnmutedUpdate](../src/Models/Response/DialogUnmutedUpdate.php).
- [**MessageCallbackEvent**](../src/MaxBot/Events/MessageCallbackEvent.php) — Событие для
  модели [MessageCallbackUpdate](../src/Models/Response/MessageCallbackUpdate.php).
- [**MessageCreatedEvent**](../src/MaxBot/Events/MessageCreatedEvent.php) — Событие для
  модели [MessageCreatedUpdate](../src/Models/Response/MessageCreatedUpdate.php).
- [**MessageEditedEvent**](../src/MaxBot/Events/MessageEditedEvent.php) — Событие для
  модели [MessageEditedUpdate](../src/Models/Response/MessageEditedUpdate.php).
- [**MessageRemovedEvent**](../src/MaxBot/Events/MessageRemovedEvent.php) — Событие для
  модели [MessageRemovedUpdate](../src/Models/Response/MessageRemovedUpdate.php).
- [**UnknownEvent**](../src/MaxBot/Events/UnknownEvent.php) — Событие для неизвестных типов обновлений.
- [**UserAddedEvent**](../src/MaxBot/Events/UserAddedEvent.php) — Событие для
  модели [UserAddedUpdate](../src/Models/Response/UserAddedUpdate.php).
- [**UserRemovedEvent**](../src/MaxBot/Events/UserRemovedEvent.php) — Событие для
  модели [UserRemovedUpdate](../src/Models/Response/UserRemovedUpdate.php).

### Конвенции

- Формат элемента списка:
    ```markdown
    - [**EventName**](../src/MaxBot/Events/EventName.php) — Событие для модели [EventUpdate](../src/Models/Response/EventUpdate.php).
    ```
- Список должен быть отсортирован по алфавиту
