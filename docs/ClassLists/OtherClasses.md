# Другие классы

## Список классов не вошедших в другие списки

Это список классов, не вошедших в списки:

- [Интерфейсы](Contracts.md)
- [Перечисления](Enums.md)
- [Исключения](Exceptions.md)
- [Http клиента](HttpClient.md)
- [События бота](MaxBotEvents.md)
- [Модели запросов](RequestModels.md)
- [Модели ответов](ResponseModels.md)

Он же, план проверки на соответствие документации на сайте <https://dev.max.ru/docs-api/>
если класс является реализацией части API.

### Основные классы

- ❌ [**MaxApiClient**](../../src/MaxApiClient.php) — Основной клиент для взаимодействия с API Max.
- ❌ [**MaxApiConfig**](../../src/MaxApiConfig.php) — Конфигурация подключения к API Max.
- ❌ [**MaxBot**](../../src/MaxBot.php) — Класс бота с обработкой команд и событий.

### Вспомогательные классы бота

- ❌ [**CallbackHandler**](../../src/MaxBot/CallbackHandler.php) — Обработчик callback-запросов.
- ❌ [**CallbackJsonHandler**](../../src/MaxBot/CallbackJsonHandler.php) — Обработчик callback JSON.
- ❌ [**CommandHandler**](../../src/MaxBot/CommandHandler.php) — Обработчик команд бота.
- ❌ [**HandlerListType**](../../src/MaxBot/HandlerListType.php) — Типы списков обработчиков событий.

### Соглашения

- Формат элемента списка:
    ```markdown
    - ❌ [**ClassName**](../../src/.../ClassName.php) — Короткое описание класса
    ```
- Добавляемые классы, нужно отмечать символом `❌`
- Списки должны быть отсортированы по алфавиту
- **Отметка `✅` ставится при выполнении всех условий:**
    - Есть короткое описание класса.
    - Все методы классов имею описание.
