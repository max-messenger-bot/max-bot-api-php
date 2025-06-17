# Перечисления

## Список перечислений

План проверки на соответствие документации на сайте <https://dev.max.ru/docs-api/>.

- ❌ [**AttachmentRequestType**](../../src/Models/Enums/AttachmentRequestType.php) — Тип вложения для сообщения.
- ❌ [**AttachmentType**](../../src/Models/Enums/AttachmentType.php) — Тип вложения для сообщения.
- ❌ [**ButtonType**](../../src/Models/Enums/ButtonType.php) — Тип кнопки.
- ❌ [**ChatAdminPermission**](../../src/Models/Enums/ChatAdminPermission.php) — Права администратора чата.
- ❌ [**ChatStatus**](../../src/Models/Enums/ChatStatus.php) — Статус чата.
- ❌ [**ChatType**](../../src/Models/Enums/ChatType.php) — Тип чата: диалог, чат, канал.
- ❌ [**EnumHelperTrait**](../../src/Models/Enums/EnumHelperTrait.php) — Трейт с вспомогательными методами для
  перечислений.
- ❌ [**Intent**](../../src/Models/Enums/Intent.php) — Намерение кнопки.
- ❌ [**MarkupElementType**](../../src/Models/Enums/MarkupElementType.php) — Тип элемента разметки.
- ❌ [**MessageLinkType**](../../src/Models/Enums/MessageLinkType.php) — Тип связанного сообщения.
- ❌ [**ReplyButtonType**](../../src/Models/Enums/ReplyButtonType.php) — Тип кнопки ответа.
- ❌ [**SenderAction**](../../src/Models/Enums/SenderAction.php) — Действие, отправляемое участникам чата.
- ❌ [**TextFormat**](../../src/Models/Enums/TextFormat.php) — Формат текста сообщения.
- ❌ [**UpdateType**](../../src/Models/Enums/UpdateType.php) — Тип обновления.
- ❌ [**UploadType**](../../src/Models/Enums/UploadType.php) — Тип загружаемого файла.

### Соглашения

- Формат элемента списка:
    ```markdown
    - ❌ [**EnumName**](../../src/Models/Enums/EnumName.php) — Первая строка описания перечисления.
    ```
- Добавляемые перечисления, нужно отмечать символом `❌`
- Список должен быть отсортирован по алфавиту
- **Отметка `✅` ставится при выполнении всех условий:**
    - Список элементов соответствует описанию на сайте <https://dev.max.ru/docs-api/>
    - Ссылка на описание модели содержится в теге `@link` описания класса.
    - Есть короткое описание класса.
    - Все комментарии на русском языке.
