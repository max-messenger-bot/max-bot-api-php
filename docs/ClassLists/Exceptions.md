# Классы исключений

## Список классов исключений

План проверки классов.

- ❌ [**ActionProhibited**](../../src/Exceptions/ActionProhibited.php) — Action prohibited.
- ❌ [**EventException**](../../src/Exceptions/EventException.php) — Event internal exception.
- ❌ [**LogicException**](../../src/Exceptions/LogicException.php) (abstract) — Base class for logic exceptions.
- ❌ [**RequireArgumentException**](../../src/Exceptions/RequireArgumentException.php) — The argument cannot be null.
- ❌ [**RequireArgumentsException**](../../src/Exceptions/RequireArgumentsException.php) — At least one argument
  must be non-null.
- ❌ [**RuntimeException**](../../src/Exceptions/RuntimeException.php) — Base class for runtime exceptions.
- ❌ [**SimpleQueryError**](../../src/Exceptions/SimpleQueryError.php) — Simple query error.
- **Validation** — Validation exceptions.
    - ❌ [**MaxItemsException**](../../src/Exceptions/Validation/MaxItemsException.php) — Array has too many items.
    - ❌ [**MaxLengthException**](../../src/Exceptions/Validation/MaxLengthException.php) — String is too long.
    - ❌ [**MatchException**](../../src/Exceptions/Validation/MatchException.php) — Argument has an invalid value.
    - ❌ [**MinItemsException**](../../src/Exceptions/Validation/MinItemsException.php) — Array has too few items.
    - ❌ [**MinLengthException**](../../src/Exceptions/Validation/MinLengthException.php) — String is too short.
    - ❌ [**MustBeLessException**](../../src/Exceptions/Validation/MustBeLessException.php) — Argument must be less
      than another.
    - ❌ [**ValidationException**](../../src/Exceptions/Validation/ValidationException.php) (abstract) — Base class
      for validation exceptions.

### Соглашения

- Формат элемента списка:
    ```markdown
    - ❌ [**ExceptionName**](../../src/Exceptions/ExceptionName.php) — Короткое описание класса
    ```
- Формат элемента списка с вложенными элементами:
    ```markdown
    - **FolderName** — Короткое описание папки
    ```

- Добавляемые классы исключений, нужно отмечать символом `❌`
- Список должен быть отсортирован по алфавиту
- **Отметка `✅` ставится при выполнении всех условий:**
    - Есть описание класса на английском языке.
    - Все элементы класса имеют комментарии на английском языке.
