# Модели запросов

## Список классов моделей запросов

План проверки на соответствие документации на сайте <https://dev.max.ru/docs-api/>.

- ❌ [**ActionRequestBody**](../../src/Models/Requests/ActionRequestBody.php) — Request to send bot action to chat.
- ❌ [**AttachmentRequest**](../../src/Models/Requests/AttachmentRequest.php) — Request to attach some data to message.
- ❌ [**AudioAttachmentRequest**](../../src/Models/Requests/AudioAttachmentRequest.php) — Request to attach audio to
  message. MUST be the only attachment in message.
- ❌ [**BaseRequestModel**](../../src/Models/Requests/BaseRequestModel.php) — Base class for request models.
- ❌ [**BotCommand**](../../src/Models/Requests/BotCommand.php) — Bot command.
- ❌ [**BotPatch**](../../src/Models/Requests/BotPatch.php) — Request to edit bot info.
- ❌ [**CallbackAnswer**](../../src/Models/Requests/CallbackAnswer.php) — Send this object when your bot wants to react
  to when a button is pressed.
- ❌ [**ChatAdmin**](../../src/Models/Requests/ChatAdmin.php) — Administrator id with permissions.
- ❌ [**ChatAdminsList**](../../src/Models/Requests/ChatAdminsList.php) — Administrators list.
- ❌ [**ChatPatch**](../../src/Models/Requests/ChatPatch.php) — Request to edit chat info.
- ❌ [**ContactAttachmentRequest**](../../src/Models/Requests/ContactAttachmentRequest.php) — Request to attach contact
  card to message. MUST be the only attachment in message.
- ❌ [**ContactAttachmentRequestPayload**](../../src/Models/Requests/ContactAttachmentRequestPayload.php) — Contact
  attachment payload.
- ❌ [**FileAttachmentRequest**](../../src/Models/Requests/FileAttachmentRequest.php) — Request to attach file to
  message. MUST be the only attachment in message.
- ❌ [**NewMessageBody**](../../src/Models/Requests/NewMessageBody.php) — New message body.
- ❌ [**NewMessageLink**](../../src/Models/Requests/NewMessageLink.php) — Link to message.
- ❌ [**PhotoAttachmentRequest**](../../src/Models/Requests/PhotoAttachmentRequest.php) — Request to attach image to
  message.
- ❌ [**PhotoAttachmentRequestPayload**](../../src/Models/Requests/PhotoAttachmentRequestPayload.php) — Request to attach
  image. All fields are mutually exclusive.
- ❌ [**PhotoToken**](../../src/Models/Requests/PhotoToken.php) — Encoded information of uploaded image.
- ❌ [**PinMessageBody**](../../src/Models/Requests/PinMessageBody.php) — Request to pin message in chat or channel.
- ❌ [**RawModel**](../../src/Models/Requests/RawModel.php) — Raw data query model.
- ❌ [**StickerAttachmentRequest**](../../src/Models/Requests/StickerAttachmentRequest.php) — Request to attach sticker.
  MUST be the only attachment request in message.
- ❌ [**StickerAttachmentRequestPayload**](../../src/Models/Requests/StickerAttachmentRequestPayload.php) — Sticker
  attachment payload.
- ❌ [**SubscriptionRequestBody**](../../src/Models/Requests/SubscriptionRequestBody.php) — Request to set up WebHook
  subscription.
- ❌ [**UploadedInfo**](../../src/Models/Requests/UploadedInfo.php) — This is information you will receive as soon as
  audio/video is uploaded.
- ❌ [**UserIdsList**](../../src/Models/Requests/UserIdsList.php) — User IDs list.
- ❌ [**ValidateTrait**](../../src/Models/Requests/ValidateTrait.php) — Trait for validation request model properties.
- ❌ [**VideoAttachmentRequest**](../../src/Models/Requests/VideoAttachmentRequest.php) — Request to attach video to
  message.

### Соглашения

- Формат элемента списка:
    ```markdown
    - ❌ [**ModelName**](../../src/Models/Requests/ModelName.php) — Короткое описание модели
    ```
- Добавляемые модели, нужно отмечать символом `❌`
- Список должен быть отсортирован по алфавиту
- **Отметка `✅` ставится при выполнении всех условий:**
    - Свойство `$data` в классе присутствует и соответствует описанию на сайте <https://dev.max.ru/docs-api/>
    - Ссылка на описание модели содержится в теге `@link` описания класса.
    - Описание класса содержит тег `@api`.
    - Все публичные методы в описании имеют тег `@api`.
    - Есть короткое описание класса.
    - Все комментарии на русском языке.
