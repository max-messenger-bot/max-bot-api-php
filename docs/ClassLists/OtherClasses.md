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

- ❌ [**CommandHandler**](../../src/MaxBot/CommandHandler.php) — Обработчик команд бота.
- ❌ [**HandlerListType**](../../src/MaxBot/HandlerListType.php) — Типы списков обработчиков событий.

### Загрузчики файлов (Uploaders)

- ❌ [**File**](../../src/Uploaders/Contents/File.php) — Представление файла для загрузки.
- ✅ [**FragmentUploadStat**](../../src/Uploaders/FragmentUploadStat.php) — Статистика загрузки фрагмента.
- ❌ [**MaxSimpleUploader**](../../src/Uploaders/MaxSimpleUploader.php) — Простой загрузчик файлов.
- ❌ [**MaxUploader**](../../src/Uploaders/MaxUploader.php) — Основной загрузчик файлов.
- ❌ [**Stream**](../../src/Uploaders/Contents/Stream.php) — Представление потока для загрузки.
- ❌ [**StringFile**](../../src/Uploaders/Contents/StringFile.php) — Представление файла из строки.

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
