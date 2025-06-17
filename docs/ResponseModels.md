# Модели ответов

## Список классов моделей ответов

План проверки на соответствие документации на сайте <https://dev.max.ru/docs-api/>.

- ❌ [**Attachment**](../src/Models/Response/Attachment.php) — Generic schema representing message attachment.
- ❌ [**AttachmentPayload**](../src/Models/Response/AttachmentPayload.php) — Generic attachment payload.
- ❌ [**AudioAttachment**](../src/Models/Response/AudioAttachment.php) — Audio attachment.
- ❌ [**BotAddedUpdate**](../src/Models/Response/BotAddedUpdate.php) — You will receive this update when bot has been
  added to chat.
- ❌ [**BotCommand**](../src/Models/Response/BotCommand.php) — Bot command.
- ✅ [**BotInfo**](../src/Models/Response/BotInfo.php) — Объект включает общую информацию о боте, URL аватара и описание.
- ❌ [**BotRemovedUpdate**](../src/Models/Response/BotRemovedUpdate.php) — You will receive this update when bot has been
  removed from chat.
- ❌ [**BotStartedUpdate**](../src/Models/Response/BotStartedUpdate.php) — Bot gets this type of update as soon as user
  pressed `Start` button.
- ❌ [**BotStoppedUpdate**](../src/Models/Response/BotStoppedUpdate.php) — Bot gets this type of update as soon as user
  stopped the bot.
- ❌ [**Button**](../src/Models/Response/Button.php) — Keyboard button.
- ❌ [**Callback**](../src/Models/Response/Callback.php) — Object sent to bot when user presses button.
- ❌ [**CallbackButton**](../src/Models/Response/CallbackButton.php) — Callback button. After pressing this type of
  button client sends to server payload it contains.
- ❌ [**Chat**](../src/Models/Response/Chat.php) — Chat information.
- ❌ [**ChatButton**](../src/Models/Response/ChatButton.php) — Chat button. Button that creates new chat as soon as the
  first user clicked on it.
- ❌ [**ChatList**](../src/Models/Response/ChatList.php) — Returns paginated response of chats.
- ✅ [**ChatMember**](../src/Models/Response/ChatMember.php) — Объект включает общую информацию о пользователе или боте,
  URL аватара и описание (при наличии).
- ❌ [**ChatMembersList**](../src/Models/Response/ChatMembersList.php) — Returns members list and pointer to the next
  data page.
- ❌ [**ChatTitleChangedUpdate**](../src/Models/Response/ChatTitleChangedUpdate.php) — Bot gets this type of update as
  soon as title has been changed in chat.
- ❌ [**ContactAttachment**](../src/Models/Response/ContactAttachment.php) — Contact attachment.
- ❌ [**ContactAttachmentPayload**](../src/Models/Response/ContactAttachmentPayload.php) — Contact attachment payload.
- ❌ [**DataAttachment**](../src/Models/Response/DataAttachment.php) — Data attachment. Attachment contains payload sent
  through `SendMessageButton`.
- ❌ [**DialogClearedUpdate**](../src/Models/Response/DialogClearedUpdate.php) — Bot gets this type of update as soon as
  dialog has been cleared.
- ❌ [**DialogMutedUpdate**](../src/Models/Response/DialogMutedUpdate.php) — Bot gets this type of update as soon as
  dialog has been muted.
- ❌ [**DialogRemovedUpdate**](../src/Models/Response/DialogRemovedUpdate.php) — Bot gets this type of update as soon as
  dialog has been removed.
- ❌ [**DialogUnmutedUpdate**](../src/Models/Response/DialogUnmutedUpdate.php) — Bot gets this type of update as soon as
  dialog has been unmuted.
- ❌ [**EmphasizedMarkup**](../src/Models/Response/EmphasizedMarkup.php) — Emphasized markup. Represents *italic* in
  text.
- ❌ [**Error**](../src/Models/Response/Error.php) — Server returns this if there was an exception to your request.
- ❌ [**FileAttachment**](../src/Models/Response/FileAttachment.php) — File attachment.
- ❌ [**FileAttachmentPayload**](../src/Models/Response/FileAttachmentPayload.php) — File attachment payload.
- ❌ [**GetPinnedMessageResult**](../src/Models/Response/GetPinnedMessageResult.php) — Pinned message result.
- ❌ [**GetSubscriptionsResult**](../src/Models/Response/GetSubscriptionsResult.php) — List of all WebHook subscriptions.
- ❌ [**HeadingMarkup**](../src/Models/Response/HeadingMarkup.php) — Heading markup. Represents header part of the text.
- ❌ [**HighlightedMarkup**](../src/Models/Response/HighlightedMarkup.php) — Highlighted markup. Represents a highlighted
  piece of text.
- ❌ [**Image**](../src/Models/Response/Image.php) — Generic schema describing image object.
- ❌ [**InlineKeyboardAttachment**](../src/Models/Response/InlineKeyboardAttachment.php) — Inline keyboard attachment.
  Buttons in messages.
- ❌ [**Keyboard**](../src/Models/Response/Keyboard.php) — Keyboard is two-dimension array of buttons.
- ❌ [**LinkButton**](../src/Models/Response/LinkButton.php) — Link button. After pressing this type of button user
  follows the link it contains.
- ❌ [**LinkedMessage**](../src/Models/Response/LinkedMessage.php) — Linked message information.
- ❌ [**LinkMarkup**](../src/Models/Response/LinkMarkup.php) — Link markup. Represents link in text.
- ❌ [**LocationAttachment**](../src/Models/Response/LocationAttachment.php) — Location attachment.
- ❌ [**MarkupElement**](../src/Models/Response/MarkupElement.php) — Markup element for text formatting.
- ❌ [**MediaAttachmentPayload**](../src/Models/Response/MediaAttachmentPayload.php) — Media attachment payload.
- ❌ [**Message**](../src/Models/Response/Message.php) — Message in chat.
- ❌ [**MessageBody**](../src/Models/Response/MessageBody.php) — Schema representing body of message.
- ❌ [**MessageButton**](../src/Models/Response/MessageButton.php) — Message button. After pressing this type of button
  it sends message from user in chat.
- ❌ [**MessageCallbackUpdate**](../src/Models/Response/MessageCallbackUpdate.php) — You will get this `update` as soon
  as user presses button.
- ❌ [**MessageCreatedUpdate**](../src/Models/Response/MessageCreatedUpdate.php) — You will get this `update` as soon as
  message is created.
- ❌ [**MessageEditedUpdate**](../src/Models/Response/MessageEditedUpdate.php) — You will get this `update` as soon as
  message is edited.
- ❌ [**MessageList**](../src/Models/Response/MessageList.php) — Paginated list of messages.
- ❌ [**MessageRemovedUpdate**](../src/Models/Response/MessageRemovedUpdate.php) — You will get this `update` as soon as
  message is removed.
- ❌ [**MessageStat**](../src/Models/Response/MessageStat.php) — Message statistics.
- ❌ [**MonospacedMarkup**](../src/Models/Response/MonospacedMarkup.php) — Monospaced markup. Represents `monospaced` or
  ```code``` block in text.
- ❌ [**PhotoAttachment**](../src/Models/Response/PhotoAttachment.php) — Image attachment.
- ❌ [**PhotoAttachmentPayload**](../src/Models/Response/PhotoAttachmentPayload.php) — Image attachment payload.
- ❌ [**Recipient**](../src/Models/Response/Recipient.php) — New message recipient. Could be user or chat.
- ❌ [**ReplyButton**](../src/Models/Response/ReplyButton.php) — Reply keyboard button. After pressing this type of
  button client will send a message on behalf of user with given payload.
- ❌ [**ReplyKeyboardAttachment**](../src/Models/Response/ReplyKeyboardAttachment.php) — Reply keyboard attachment.
  Custom reply keyboard in message.
- ❌ [**RequestContactButton**](../src/Models/Response/RequestContactButton.php) — Request contact button. After pressing
  this type of button client sends new message with attachment of current user contact.
- ❌ [**RequestGeoLocationButton**](../src/Models/Response/RequestGeoLocationButton.php) — Request geo location button.
  After pressing this type of button client sends new message with attachment of current user geo location.
- ❌ [**SendContactButton**](../src/Models/Response/SendContactButton.php) — Send contact reply button. After pressing
  this type of button client sends new message with attachment of current user contact.
- ❌ [**SendGeoLocationButton**](../src/Models/Response/SendGeoLocationButton.php) — Send geo location reply button.
  After pressing this type of button client sends new message with attachment of current user geo location.
- ❌ [**SendMessageButton**](../src/Models/Response/SendMessageButton.php) — Send message reply button. After pressing
  this type of button client will send a message on behalf of user with given payload.
- ❌ [**SendMessageResult**](../src/Models/Response/SendMessageResult.php) — Result of sending a message.
- ❌ [**ShareAttachment**](../src/Models/Response/ShareAttachment.php) — Share attachment.
- ❌ [**ShareAttachmentPayload**](../src/Models/Response/ShareAttachmentPayload.php) — Payload of ShareAttachmentRequest
  and ShareAttachment.
- ❌ [**SimpleQueryResult**](../src/Models/Response/SimpleQueryResult.php) — Simple response to request.
- ❌ [**StickerAttachment**](../src/Models/Response/StickerAttachment.php) — Sticker attachment.
- ❌ [**StickerAttachmentPayload**](../src/Models/Response/StickerAttachmentPayload.php) — Sticker attachment payload.
- ❌ [**StrikethroughMarkup**](../src/Models/Response/StrikethroughMarkup.php) — Strikethrough markup. Represents ~
  strikethrough~ block in text.
- ❌ [**StrongMarkup**](../src/Models/Response/StrongMarkup.php) — Strong markup. Represents **bold** in text.
- ❌ [**Subscription**](../src/Models/Response/Subscription.php) — Schema to describe WebHook subscription.
- ❌ [**UnderlineMarkup**](../src/Models/Response/UnderlineMarkup.php) — Underline markup. Represents ++underlined++ part
  of the text.
- ❌ [**Update**](../src/Models/Response/Update.php) — `Update` object represents different types of events that happened
  in chat. See its inheritors.
- ❌ [**UpdateList**](../src/Models/Response/UpdateList.php) — List of all updates in chats your bot participated in.
- ❌ [**UploadEndpoint**](../src/Models/Response/UploadEndpoint.php) — Endpoint you should upload to your binaries.
- ✅ [**User**](../src/Models/Response/User.php) — Объект содержит общую информацию о пользователе или боте без аватара.
- ❌ [**UserAddedUpdate**](../src/Models/Response/UserAddedUpdate.php) — You will receive this update when user has been
  added to chat where bot is administrator.
- ❌ [**UserMentionMarkup**](../src/Models/Response/UserMentionMarkup.php) — User mention markup. Represents user mention
  in text.
- ❌ [**UserRemovedUpdate**](../src/Models/Response/UserRemovedUpdate.php) — You will receive this update when user has
  been removed from chat where bot is administrator.
- ✅ [**UserWithPhoto**](../src/Models/Response/UserWithPhoto.php) — Объект с общей информацией о пользователе или боте,
  дополнительно содержит URL аватара и описание.
- ❌ [**VideoAttachment**](../src/Models/Response/VideoAttachment.php) — Video attachment.
- ❌ [**VideoAttachmentDetails**](../src/Models/Response/VideoAttachmentDetails.php) — Detailed video attachment info.
- ❌ [**VideoThumbnail**](../src/Models/Response/VideoThumbnail.php) — Video thumbnail.
- ❌ [**VideoUrls**](../src/Models/Response/VideoUrls.php) — Video URLs in different resolutions.

### Конвенции

- Формат элемента списка:
    ```markdown
    - ❌ [**ModelName**](../src/Models/Response/ModelName.php) — Короткое описание модели
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
