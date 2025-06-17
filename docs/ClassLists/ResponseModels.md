# Модели ответов

## Список классов моделей ответов

План проверки на соответствие документации на сайте <https://dev.max.ru/docs-api/>.

- ❌ [**Attachment**](../../src/Models/Responses/Attachment.php) — Общая схема, представляющая вложение сообщения.
- ❌ [**AttachmentPayload**](../../src/Models/Responses/AttachmentPayload.php) — Базовый класс для полезной нагрузки
  вложений.
- ❌ [**AudioAttachment**](../../src/Models/Responses/AudioAttachment.php) — Аудио вложение.
- ❌ [**BaseResponseModel**](../../src/Models/Responses/BaseResponseModel.php) — Базовый класс для моделей ответов.
- ❌ [**BotAddedToChatUpdate**](../../src/Models/Responses/BotAddedToChatUpdate.php) — Вы получите этот update, как
  только бот будет добавлен в чат.
- ❌ [**BotCommand**](../../src/Models/Responses/BotCommand.php) — Команды, поддерживаемые ботом.
- ❌ [**BotInfo**](../../src/Models/Responses/BotInfo.php) — Объект включает общую информацию о боте, URL аватара и
  описание.
- ❌ [**BotRemovedFromChatUpdate**](../../src/Models/Responses/BotRemovedFromChatUpdate.php) — Вы получите этот update,
  как только бот будет удалён из чата.
- ❌ [**BotStartedUpdate**](../../src/Models/Responses/BotStartedUpdate.php) — Бот получает этот тип обновления, как
  только пользователь нажал кнопку `Start`.
- ❌ [**BotStoppedUpdate**](../../src/Models/Responses/BotStoppedUpdate.php) — Бот получает этот тип обновления, как
  только пользователь останавливает бота.
- ❌ [**Button**](../../src/Models/Responses/Button.php) — Базовый класс кнопки.
- ❌ [**Callback**](../../src/Models/Responses/Callback.php) — Объект, отправленный боту, когда пользователь нажимает
  кнопку.
- ❌ [**CallbackButton**](../../src/Models/Responses/CallbackButton.php) — Callback-кнопка.
- ❌ [**Chat**](../../src/Models/Responses/Chat.php) — Информация о групповом чате или канале.
- ❌ [**ChatButton**](../../src/Models/Responses/ChatButton.php) — Кнопка создания чата.
- ❌ [**ChatList**](../../src/Models/Responses/ChatList.php) — Список чатов и указатель на следующую страницу данных.
- ❌ [**ChatMember**](../../src/Models/Responses/ChatMember.php) — Объект включает общую информацию о пользователе или
  боте, URL аватара и описание (при наличии).
- ❌ [**ChatMembersList**](../../src/Models/Responses/ChatMembersList.php) — Список участников и указатель на следующую
  страницу данных.
- ❌ [**ChatTitleChangedUpdate**](../../src/Models/Responses/ChatTitleChangedUpdate.php) — Бот получит это обновление,
  когда будет изменено название чата.
- ❌ [**ClipboardButton**](../../src/Models/Responses/ClipboardButton.php) — Кнопка буфера обмена.
- ❌ [**ContactAttachment**](../../src/Models/Responses/ContactAttachment.php) — Контактное вложение.
- ❌ [**ContactAttachmentPayload**](../../src/Models/Responses/ContactAttachmentPayload.php) — Полезная нагрузка
  контактного вложения.
- ❌ [**DataAttachment**](../../src/Models/Responses/DataAttachment.php) — Вложение содержит полезную нагрузку,
  отправленную через `SendMessageButton`.
- ❌ [**DialogClearedUpdate**](../../src/Models/Responses/DialogClearedUpdate.php) — Бот получает этот тип обновления
  сразу после очистки истории диалога.
- ❌ [**DialogMutedUpdate**](../../src/Models/Responses/DialogMutedUpdate.php) — Вы получите этот update, когда
  пользователь заглушит диалог с ботом.
- ❌ [**DialogRemovedUpdate**](../../src/Models/Responses/DialogRemovedUpdate.php) — Вы получите этот update, когда
  пользователь удаляет чат.
- ❌ [**DialogUnmutedUpdate**](../../src/Models/Responses/DialogUnmutedUpdate.php) — Вы получите этот update, когда
  пользователь включит уведомления в диалоге с ботом.
- ❌ [**EmphasizedMarkup**](../../src/Models/Responses/EmphasizedMarkup.php) — Представляет \*курсив\*.
- ❌ [**Error**](../../src/Models/Responses/Error.php) — Сервер возвращает это, если возникло исключение при вашем
  запросе.
- ❌ [**FailedUserDetails**](../../src/Models/Responses/FailedUserDetails.php) — Подробное описание, почему пользователь
  не был добавлен в чат.
- ❌ [**FileAttachment**](../../src/Models/Responses/FileAttachment.php) — Файловое вложение.
- ❌ [**FileAttachmentPayload**](../../src/Models/Responses/FileAttachmentPayload.php) — Полезная нагрузка файлового
  вложения.
- ❌ [**GetPinnedMessageResult**](../../src/Models/Responses/GetPinnedMessageResult.php) — Результат получения
  закреплённого сообщения.
- ❌ [**GetSubscriptionsResult**](../../src/Models/Responses/GetSubscriptionsResult.php) — Список всех WebHook подписок.
- ❌ [**HeadingMarkup**](../../src/Models/Responses/HeadingMarkup.php) — Представляет заголовок текста.
- ❌ [**HighlightedMarkup**](../../src/Models/Responses/HighlightedMarkup.php) — Представляет выделенную часть текста.
- ❌ [**Image**](../../src/Models/Responses/Image.php) — Общая схема, описывающая объект изображения.
- ❌ [**InlineKeyboardAttachment**](../../src/Models/Responses/InlineKeyboardAttachment.php) — Кнопки в сообщении.
- ❌ [**Keyboard**](../../src/Models/Responses/Keyboard.php) — Клавиатура - это двумерный массив кнопок.
- ❌ [**LinkButton**](../../src/Models/Responses/LinkButton.php) — Кнопка-ссылка.
- ❌ [**LinkedMessage**](../../src/Models/Responses/LinkedMessage.php) — Информация о связанном сообщении.
- ❌ [**LinkMarkup**](../../src/Models/Responses/LinkMarkup.php) — Представляет ссылку в тексте.
- ❌ [**LocationAttachment**](../../src/Models/Responses/LocationAttachment.php) — Вложение локации.
- ❌ [**MarkupElement**](../../src/Models/Responses/MarkupElement.php) — Тип элемента разметки.
- ❌ [**MediaAttachmentPayload**](../../src/Models/Responses/MediaAttachmentPayload.php) — Полезная нагрузка медиа
  вложения.
- ❌ [**Message**](../../src/Models/Responses/Message.php) — Сообщение в чате.
- ❌ [**MessageBody**](../../src/Models/Responses/MessageBody.php) — Схема, представляющая тело сообщения.
- ❌ [**MessageButton**](../../src/Models/Responses/MessageButton.php) — Кнопка сообщения.
- ❌ [**MessageCallbackUpdate**](../../src/Models/Responses/MessageCallbackUpdate.php) — Вы получите этот `update` как
  только пользователь нажмёт кнопку.
- ❌ [**MessageCreatedUpdate**](../../src/Models/Responses/MessageCreatedUpdate.php) — Вы получите этот `update`, как
  только сообщение будет создано.
- ❌ [**MessageEditedUpdate**](../../src/Models/Responses/MessageEditedUpdate.php) — Вы получите этот `update`, как
  только сообщение будет отредактировано.
- ❌ [**MessageList**](../../src/Models/Responses/MessageList.php) — Пагинированный список сообщений.
- ❌ [**MessageRemovedUpdate**](../../src/Models/Responses/MessageRemovedUpdate.php) — Вы получите этот `update`, как
  только сообщение будет удалено.
- ❌ [**MessageStat**](../../src/Models/Responses/MessageStat.php) — Статистика сообщения.
- ❌ [**ModifyMembersResult**](../../src/Models/Responses/ModifyMembersResult.php) — Результат запроса на изменение
  списка участников.
- ❌ [**MonospacedMarkup**](../../src/Models/Responses/MonospacedMarkup.php) — Представляет \`моноширинный\` или блок
  \`\`\`код\`\`\` в тексте.
- ❌ [**PhotoAttachment**](../../src/Models/Responses/PhotoAttachment.php) — Вложение изображения.
- ❌ [**PhotoAttachmentPayload**](../../src/Models/Responses/PhotoAttachmentPayload.php) — Полезная нагрузка вложения
  изображения.
- ❌ [**PhotoToken**](../../src/Models/Responses/PhotoToken.php) — Закодированная информация загруженного изображения.
- ❌ [**PhotoTokens**](../../src/Models/Responses/PhotoTokens.php) — Информация о загруженных изображениях.
- ❌ [**Recipient**](../../src/Models/Responses/Recipient.php) — Новый получатель сообщения.
- ❌ [**ReplyButton**](../../src/Models/Responses/ReplyButton.php) — Кнопка ответа.
- ❌ [**ReplyKeyboardAttachment**](../../src/Models/Responses/ReplyKeyboardAttachment.php) — Custom reply keyboard in
  message.
- ❌ [**RequestContactButton**](../../src/Models/Responses/RequestContactButton.php) — Кнопка запроса контакта.
- ❌ [**RequestGeoLocationButton**](../../src/Models/Responses/RequestGeoLocationButton.php) — Кнопка запроса геолокации.
- ❌ [**SendContactButton**](../../src/Models/Responses/SendContactButton.php) — Кнопка отправки контакта.
- ❌ [**SendGeoLocationButton**](../../src/Models/Responses/SendGeoLocationButton.php) — Кнопка отправки геолокации.
- ❌ [**SendMessageButton**](../../src/Models/Responses/SendMessageButton.php) — Кнопка отправки сообщения.
- ❌ [**SendMessageResult**](../../src/Models/Responses/SendMessageResult.php) — Информация о созданном сообщении.
- ❌ [**ShareAttachment**](../../src/Models/Responses/ShareAttachment.php) — Вложение Share.
- ❌ [**ShareAttachmentPayload**](../../src/Models/Responses/ShareAttachmentPayload.php) — Полезная нагрузка запроса
  ShareAttachmentRequest.
- ❌ [**SimpleQueryResult**](../../src/Models/Responses/SimpleQueryResult.php) — Простой ответ на запрос.
- ❌ [**StickerAttachment**](../../src/Models/Responses/StickerAttachment.php) — Вложение стикера.
- ❌ [**StickerAttachmentPayload**](../../src/Models/Responses/StickerAttachmentPayload.php) — Полезная нагрузка вложения
  стикера.
- ❌ [**StrikethroughMarkup**](../../src/Models/Responses/StrikethroughMarkup.php) — Представляет \~зачеркнутый\~ текст.
- ❌ [**StrongMarkup**](../../src/Models/Responses/StrongMarkup.php) — Представляет \*\*жирный\*\* текст.
- ❌ [**Subscription**](../../src/Models/Responses/Subscription.php) — Схема для описания подписки на WebHook.
- ❌ [**UnderlineMarkup**](../../src/Models/Responses/UnderlineMarkup.php) — Представляет \<ins>подчеркнутый\</ins>
  текст.
- ❌ [**Update**](../../src/Models/Responses/Update.php) — Объект `Update` представляет различные типы событий,
  произошедших в чате.
- ❌ [**UpdateList**](../../src/Models/Responses/UpdateList.php) — Список всех обновлений в чатах, в которых ваш бот
  участвовал.
- ❌ [**UploadedFile**](../../src/Models/Responses/UploadedFile.php) — Загруженный файл.
- ❌ [**UploadEndpoint**](../../src/Models/Responses/UploadEndpoint.php) — Точка доступа, куда следует загружать ваши
  бинарные файлы.
- ❌ [**User**](../../src/Models/Responses/User.php) — Объект содержит общую информацию о пользователе или боте без
  аватара.
- ❌ [**UserAddedToChatUpdate**](../../src/Models/Responses/UserAddedToChatUpdate.php) — Вы получите это обновление,
  когда пользователь будет добавлен в чат, где бот является администратором.
- ❌ [**UserMentionMarkup**](../../src/Models/Responses/UserMentionMarkup.php) — Представляет упоминание пользователя в
  тексте.
- ❌ [**UserRemovedFromChatUpdate**](../../src/Models/Responses/UserRemovedFromChatUpdate.php) — Вы получите это
  обновление, когда пользователь будет удалён из чата, где бот является администратором.
- ❌ [**UserWithPhoto**](../../src/Models/Responses/UserWithPhoto.php) — Объект с общей информацией о пользователе или
  боте, дополнительно содержит URL аватара и описание.
- ❌ [**VideoAttachment**](../../src/Models/Responses/VideoAttachment.php) — Видео вложение.
- ❌ [**VideoAttachmentDetails**](../../src/Models/Responses/VideoAttachmentDetails.php) — Детальная информация о видео
  вложении.
- ❌ [**VideoThumbnail**](../../src/Models/Responses/VideoThumbnail.php) — Миниатюра видео.
- ❌ [**VideoUrls**](../../src/Models/Responses/VideoUrls.php) — Видео URL в разных разрешениях.

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
    - Есть короткое описание класса.
    - Все комментарии на русском языке.
