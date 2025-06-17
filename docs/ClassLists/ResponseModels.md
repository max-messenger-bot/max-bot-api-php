# Модели ответов

## Список классов моделей ответов

План проверки на соответствие документации на сайте <https://dev.max.ru/docs-api/>.

- ❌ [**Attachment**](../../src/Models/Responses/Attachment.php) — Generic schema representing message attachment.
- ❌ [**AttachmentPayload**](../../src/Models/Responses/AttachmentPayload.php) — Generic attachment payload.
- ❌ [**AudioAttachment**](../../src/Models/Responses/AudioAttachment.php) — Audio attachment.
- ❌ [**BotAddedUpdate**](../../src/Models/Responses/BotAddedUpdate.php) — You will receive this update when bot has been
  added to chat.
- ❌ [**BotCommand**](../../src/Models/Responses/BotCommand.php) — Bot command.
- ✅ [**BotInfo**](../../src/Models/Responses/BotInfo.php) — Объект включает общую информацию о боте, URL аватара и
  описание.
- ❌ [**BotRemovedUpdate**](../../src/Models/Responses/BotRemovedUpdate.php) — You will receive this update when bot has
  been removed from chat.
- ❌ [**BotStartedUpdate**](../../src/Models/Responses/BotStartedUpdate.php) — Bot gets this type of update as soon as
  user pressed `Start` button.
- ❌ [**BotStoppedUpdate**](../../src/Models/Responses/BotStoppedUpdate.php) — Bot gets this type of update as soon as
  user stopped the bot.
- ❌ [**Button**](../../src/Models/Responses/Button.php) — Keyboard button.
- ❌ [**Callback**](../../src/Models/Responses/Callback.php) — Object sent to bot when user presses button.
- ❌ [**CallbackButton**](../../src/Models/Responses/CallbackButton.php) — Callback button. After pressing this type of
  button client sends to server payload it contains.
- ❌ [**Chat**](../../src/Models/Responses/Chat.php) — Chat information.
- ❌ [**ChatButton**](../../src/Models/Responses/ChatButton.php) — Chat button. Button that creates new chat as soon as
  the first user clicked on it.
- ❌ [**ChatList**](../../src/Models/Responses/ChatList.php) — Returns paginated response of chats.
- ✅ [**ChatMember**](../../src/Models/Responses/ChatMember.php) — Объект включает общую информацию о пользователе или
  боте, URL аватара и описание (при наличии).
- ❌ [**ChatMembersList**](../../src/Models/Responses/ChatMembersList.php) — Returns members list and pointer to the next
  data page.
- ❌ [**ChatTitleChangedUpdate**](../../src/Models/Responses/ChatTitleChangedUpdate.php) — Bot gets this type of update
  as soon as title has been changed in chat.
- ❌ [**ContactAttachment**](../../src/Models/Responses/ContactAttachment.php) — Contact attachment.
- ❌ [**ContactAttachmentPayload**](../../src/Models/Responses/ContactAttachmentPayload.php) — Contact attachment
  payload.
- ❌ [**DataAttachment**](../../src/Models/Responses/DataAttachment.php) — Data attachment. Attachment contains payload
  sent through `SendMessageButton`.
- ❌ [**DialogClearedUpdate**](../../src/Models/Responses/DialogClearedUpdate.php) — Bot gets this type of update as soon
  as dialog has been cleared.
- ❌ [**DialogMutedUpdate**](../../src/Models/Responses/DialogMutedUpdate.php) — Bot gets this type of update as soon as
  dialog has been muted.
- ❌ [**DialogRemovedUpdate**](../../src/Models/Responses/DialogRemovedUpdate.php) — Bot gets this type of update as soon
  as dialog has been removed.
- ❌ [**DialogUnmutedUpdate**](../../src/Models/Responses/DialogUnmutedUpdate.php) — Bot gets this type of update as soon
  as dialog has been unmuted.
- ❌ [**EmphasizedMarkup**](../../src/Models/Responses/EmphasizedMarkup.php) — Emphasized markup. Represents *italic* in
  text.
- ❌ [**Error**](../../src/Models/Responses/Error.php) — Server returns this if there was an exception to your request.
- ❌ [**FileAttachment**](../../src/Models/Responses/FileAttachment.php) — File attachment.
- ❌ [**FileAttachmentPayload**](../../src/Models/Responses/FileAttachmentPayload.php) — File attachment payload.
- ❌ [**GetPinnedMessageResult**](../../src/Models/Responses/GetPinnedMessageResult.php) — Pinned message result.
- ❌ [**GetSubscriptionsResult**](../../src/Models/Responses/GetSubscriptionsResult.php) — List of all WebHook
  subscriptions.
- ❌ [**HeadingMarkup**](../../src/Models/Responses/HeadingMarkup.php) — Heading markup. Represents header part of the
  text.
- ❌ [**HighlightedMarkup**](../../src/Models/Responses/HighlightedMarkup.php) — Highlighted markup. Represents a
  highlighted piece of text.
- ❌ [**Image**](../../src/Models/Responses/Image.php) — Generic schema describing image object.
- ❌ [**InlineKeyboardAttachment**](../../src/Models/Responses/InlineKeyboardAttachment.php) — Inline keyboard
  attachment. Buttons in messages.
- ❌ [**Keyboard**](../../src/Models/Responses/Keyboard.php) — Keyboard is two-dimension array of buttons.
- ❌ [**LinkButton**](../../src/Models/Responses/LinkButton.php) — Link button. After pressing this type of button user
  follows the link it contains.
- ❌ [**LinkedMessage**](../../src/Models/Responses/LinkedMessage.php) — Linked message information.
- ❌ [**LinkMarkup**](../../src/Models/Responses/LinkMarkup.php) — Link markup. Represents link in text.
- ❌ [**LocationAttachment**](../../src/Models/Responses/LocationAttachment.php) — Location attachment.
- ❌ [**MarkupElement**](../../src/Models/Responses/MarkupElement.php) — Markup element for text formatting.
- ❌ [**MediaAttachmentPayload**](../../src/Models/Responses/MediaAttachmentPayload.php) — Media attachment payload.
- ❌ [**Message**](../../src/Models/Responses/Message.php) — Message in chat.
- ❌ [**MessageBody**](../../src/Models/Responses/MessageBody.php) — Schema representing body of message.
- ❌ [**MessageButton**](../../src/Models/Responses/MessageButton.php) — Message button. After pressing this type of
  button it sends message from user in chat.
- ❌ [**MessageCallbackUpdate**](../../src/Models/Responses/MessageCallbackUpdate.php) — You will get this `update` as
  soon as user presses button.
- ❌ [**MessageCreatedUpdate**](../../src/Models/Responses/MessageCreatedUpdate.php) — You will get this `update` as soon
  as message is created.
- ❌ [**MessageEditedUpdate**](../../src/Models/Responses/MessageEditedUpdate.php) — You will get this `update` as soon
  as message is edited.
- ❌ [**MessageList**](../../src/Models/Responses/MessageList.php) — Paginated list of messages.
- ❌ [**MessageRemovedUpdate**](../../src/Models/Responses/MessageRemovedUpdate.php) — You will get this `update` as soon
  as message is removed.
- ❌ [**MessageStat**](../../src/Models/Responses/MessageStat.php) — Message statistics.
- ❌ [**MonospacedMarkup**](../../src/Models/Responses/MonospacedMarkup.php) — Monospaced markup. Represents `monospaced`
  or ```code``` block in text.
- ❌ [**PhotoAttachment**](../../src/Models/Responses/PhotoAttachment.php) — Image attachment.
- ❌ [**PhotoAttachmentPayload**](../../src/Models/Responses/PhotoAttachmentPayload.php) — Image attachment payload.
- ❌ [**Recipient**](../../src/Models/Responses/Recipient.php) — New message recipient. Could be user or chat.
- ❌ [**ReplyButton**](../../src/Models/Responses/ReplyButton.php) — Reply keyboard button. After pressing this type of
  button client will send a message on behalf of user with given payload.
- ❌ [**ReplyKeyboardAttachment**](../../src/Models/Responses/ReplyKeyboardAttachment.php) — Reply keyboard attachment.
  Custom reply keyboard in message.
- ❌ [**RequestContactButton**](../../src/Models/Responses/RequestContactButton.php) — Request contact button. After
  pressing this type of button client sends new message with attachment of current user contact.
- ❌ [**RequestGeoLocationButton**](../../src/Models/Responses/RequestGeoLocationButton.php) — Request geo location
  button. After pressing this type of button client sends new message with attachment of current user geo location.
- ❌ [**SendContactButton**](../../src/Models/Responses/SendContactButton.php) — Send contact reply button. After
  pressing this type of button client sends new message with attachment of current user contact.
- ❌ [**SendGeoLocationButton**](../../src/Models/Responses/SendGeoLocationButton.php) — Send geo location reply button.
  After pressing this type of button client sends new message with attachment of current user geo location.
- ❌ [**SendMessageButton**](../../src/Models/Responses/SendMessageButton.php) — Send message reply button. After
  pressing this type of button client will send a message on behalf of user with given payload.
- ❌ [**SendMessageResult**](../../src/Models/Responses/SendMessageResult.php) — Result of sending a message.
- ❌ [**ShareAttachment**](../../src/Models/Responses/ShareAttachment.php) — Share attachment.
- ❌ [**ShareAttachmentPayload**](../../src/Models/Responses/ShareAttachmentPayload.php) — Payload of
  ShareAttachmentRequest and ShareAttachment.
- ❌ [**SimpleQueryResult**](../../src/Models/Responses/SimpleQueryResult.php) — Simple response to request.
- ❌ [**StickerAttachment**](../../src/Models/Responses/StickerAttachment.php) — Sticker attachment.
- ❌ [**StickerAttachmentPayload**](../../src/Models/Responses/StickerAttachmentPayload.php) — Sticker attachment
  payload.
- ❌ [**StrikethroughMarkup**](../../src/Models/Responses/StrikethroughMarkup.php) — Strikethrough markup. Represents ~
  strikethrough~ block in text.
- ❌ [**StrongMarkup**](../../src/Models/Responses/StrongMarkup.php) — Strong markup. Represents **bold** in text.
- ❌ [**Subscription**](../../src/Models/Responses/Subscription.php) — Schema to describe WebHook subscription.
- ❌ [**UnderlineMarkup**](../../src/Models/Responses/UnderlineMarkup.php) — Underline markup. Represents ++underlined++
  part of the text.
- ❌ [**Update**](../../src/Models/Responses/Update.php) — `Update` object represents different types of events that
  happened in chat. See its inheritors.
- ❌ [**UpdateList**](../../src/Models/Responses/UpdateList.php) — List of all updates in chats your bot participated in.
- ❌ [**UploadEndpoint**](../../src/Models/Responses/UploadEndpoint.php) — Endpoint you should upload to your binaries.
- ✅ [**User**](../../src/Models/Responses/User.php) — Объект содержит общую информацию о пользователе или боте без
  аватара.
- ❌ [**UserAddedUpdate**](../../src/Models/Responses/UserAddedUpdate.php) — You will receive this update when user has
  been added to chat where bot is administrator.
- ❌ [**UserMentionMarkup**](../../src/Models/Responses/UserMentionMarkup.php) — User mention markup. Represents user
  mention in text.
- ❌ [**UserRemovedUpdate**](../../src/Models/Responses/UserRemovedUpdate.php) — You will receive this update when user
  has been removed from chat where bot is administrator.
- ✅ [**UserWithPhoto**](../../src/Models/Responses/UserWithPhoto.php) — Объект с общей информацией о пользователе или
  боте, дополнительно содержит URL аватара и описание.
- ❌ [**VideoAttachment**](../../src/Models/Responses/VideoAttachment.php) — Video attachment.
- ❌ [**VideoAttachmentDetails**](../../src/Models/Responses/VideoAttachmentDetails.php) — Detailed video attachment
  info.
- ❌ [**VideoThumbnail**](../../src/Models/Responses/VideoThumbnail.php) — Video thumbnail.
- ❌ [**VideoUrls**](../../src/Models/Responses/VideoUrls.php) — Video URLs in different resolutions.

### Соглашения

- Формат элемента списка:
    ```markdown
    - ❌ [**ModelName**](../../src/Models/Responses/ModelName.php) — Короткое описание модели
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
