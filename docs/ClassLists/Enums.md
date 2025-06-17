# Перечисления

## Список перечислений

План проверки на соответствие документации на сайте <https://dev.max.ru/docs-api/>.

- ❌ [**AttachmentRequestType**](../../src/Models/Enums/AttachmentRequestType.php) — Type of attachment request.
- ❌ [**AttachmentType**](../../src/Models/Enums/AttachmentType.php) — Type of attachment.
- ❌ [**ButtonType**](../../src/Models/Enums/ButtonType.php) — Type of button.
- ✅ [**ChatAdminPermission**](../../src/Models/Enums/ChatAdminPermission.php) — Перечень прав администратора чата.
- ❌ [**ChatStatus**](../../src/Models/Enums/ChatStatus.php) — Chat status.
- ❌ [**ChatType**](../../src/Models/Enums/ChatType.php) — Type of chat. Dialog (one-on-one), chat or channel.
- ❌ [**EnumHelperTrait**](../../src/Models/Enums/EnumHelperTrait.php) — Trait for enum helper methods.
- ❌ [**Intent**](../../src/Models/Enums/Intent.php) — Intent of button.
- ❌ [**MarkupElementType**](../../src/Models/Enums/MarkupElementType.php) — Type of the markup element.
- ❌ [**MessageLinkType**](../../src/Models/Enums/MessageLinkType.php) — Type of linked message.
- ❌ [**ReplyButtonType**](../../src/Models/Enums/ReplyButtonType.php) — Type of reply button.
- ❌ [**SenderAction**](../../src/Models/Enums/SenderAction.php) — Different actions to send to chat members.
- ❌ [**TextFormat**](../../src/Models/Enums/TextFormat.php) — Message text format.
- ❌ [**UpdateType**](../../src/Models/Enums/UpdateType.php) — Type of update.
- ❌ [**UploadType**](../../src/Models/Enums/UploadType.php) — Type of file uploading.

### Соглашения

- Формат элемента списка:
    ```markdown
    - ❌ [**EnumName**](../../src/Models/Enums/EnumName.php) — Короткое описание перечисления
    ```
- Добавляемые перечисления, нужно отмечать символом `❌`
- Список должен быть отсортирован по алфавиту
- **Отметка `✅` ставится при выполнении всех условий:**
    - Список элементов соответствует описанию на сайте <https://dev.max.ru/docs-api/>
    - Ссылка на описание модели содержится в теге `@link` описания класса.
    - Описание класса содержит тег `@api`.
    - Есть короткое описание класса.
    - Все комментарии на русском языке.
