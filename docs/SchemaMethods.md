# Schema Methods

Список методов MaxApiClient из схемы (на основе которой генерировалось первоначальное API).

Сама схема устарела и не соответствует текущей реализации API.

## Список методов MaxApiClient реализующих операции Max API

| Статус | Метод                         | Описание              | Версия | Тест |
|:------:|-------------------------------|-----------------------|:------:|:----:|
|   ✅    | **addMembers**                | Add members           | 0.0.10 |  ❌   |
|   ✅    | **answerOnCallback**          | Answer on callback    | 0.0.10 |  ❌   |
|   ✅    | **deleteAdmins**              | Revoke admin rights   | 0.0.10 |  ❌   |
|   ✅    | **deleteChat**                | Delete chat           | 0.0.10 |  ❌   |
|   ✅    | **deleteMessage**             | Delete message        | 0.0.10 |  ❌   |
|   ✅    | **editChat**                  | Edit chat info        | 0.0.10 |  ❌   |
|   ✅    | **editMessage**               | Edit message          | 0.0.10 |  ❌   |
|   ✅    | **editMyInfo**                | Edit current bot info | 0.0.10 |  ❌   |
|   ✅    | **getAdmins**                 | Get chat admins       | 0.0.10 |  ❌   |
|   ✅    | **getChat**                   | Get chat              | 0.0.10 |  ❌   |
|   ✅    | **getChatByLink**             | Get chat by link      | 0.0.10 |  ❌   |
|   ✅    | **getChats**                  | Get all chats         | 0.0.10 |  ❌   |
|   ✅    | **getMessageById**            | Get message           | 0.0.10 |  ❌   |
|   ✅    | **getMembers**                | Get members           | 0.0.10 |  ❌   |
|   ✅    | **getMembership**             | Get chat membership   | 0.0.10 |  ❌   |
|   ✅    | **getMessages**               | Get messages          | 0.0.10 |  ❌   |
|   ✅    | **getPinnedMessage**          | Get pinned message    | 0.0.10 |  ❌   |
|   ✅    | **getSubscriptions**          | Get subscriptions     | 0.0.10 |  ❌   |
|   ✅    | **getUploadUrl**              | Get upload URL        | 0.0.10 |  ❌   |
|   ✅    | **getUpdates**                | Get updates           | 0.0.10 |  ❌   |
|   ✅    | **getVideoAttachmentDetails** | Get video details     | 0.0.10 |  ❌   |
|   ✅    | **getMyInfo**                 | Get current bot info  | 0.0.10 |  ❌   |
|   ✅    | **leaveChat**                 | Leave chat            | 0.0.10 |  ❌   |
|   ✅    | **pinMessage**                | Pin message           | 0.0.10 |  ❌   |
|   ✅    | **postAdmins**                | Set chat admins       | 0.0.10 |  ❌   |
|   ✅    | **removeMember**              | Remove member         | 0.0.10 |  ❌   |
|   ✅    | **sendAction**                | Send action           | 0.0.10 |  ❌   |
|   ✅    | **sendMessage**               | Send message          | 0.0.10 |  ❌   |
|   ✅    | **subscribe**                 | Subscribe             | 0.0.10 |  ❌   |
|   ✅    | **unsubscribe**               | Unsubscribe           | 0.0.10 |  ❌   |
|   ✅    | **unpinMessage**              | Unpin message         | 0.0.10 |  ❌   |

## Инструкция по обновлению файла

### Источник данных

Данные для таблицы берутся из файла `schemes/schema_0_0_10.yaml`.

### Как обновлять таблицу

1. **Открой схему API:** `schemes/schema_X_Y_Z.yaml` (где X_Y_Z — новая версия схемы)

2. **Найди все операции:** В секции `paths:` найди все методы HTTP (get, post, put, delete, patch) у каждого endpoint

3. **Извлеките данные:** Для каждой операции извлеки:
    - `operationId` — идентификатор операции (используется для именования методов в клиенте)
    - `summary` — краткое описание операции

4. **Обнови таблицу:**
    - Добавь новые методы в таблицу
    - Отметь реализованные и проверенные методы символом: ✅
    - Методы, требующие реализации или обновления, отметь символом: ❌
    - Отсортируй таблицу по столбцу «Метод» в алфавитном порядке
    - В столбце «Версия» укажи версию схемы, в которой операция была добавлена или обновлена

5. **Формат строки таблицы:**
    ```markdown
    | ❌ | **operationId** | Summary описание | X.Y.Z | ❌ |
    ```

### Пример обновления

Если в схеме появилась новая операция:

```yaml
/messages/reactions:
    post:
        operationId: addReactions
        summary: Add reactions to message
```

Добавь в таблицу (в соответствующую позицию по алфавиту):

```markdown
| ❌ | **addReactions** | Add reactions to message | 0.0.11 | ❌ |
```

### Примечания

- Текущая версия API: 0.0.10
- При обновлении версии API проверяй изменения в списке операций
- Таблица должна быть отсортирована по столбцу «Метод» в алфавитном порядке
- В столбце «Версия» указывается версия схемы, в которой операция была добавлена или последний раз изменена
- Если операция удалена из схемы, удали её из таблицы или отметь как устаревшую
