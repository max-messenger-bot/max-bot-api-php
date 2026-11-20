<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot;

use Closure;
use JsonException;
use MaxMessenger\Bot\Exception\MaxBot\Update\BadRequestException;
use MaxMessenger\Bot\Exception\MaxBot\Update\InvalidSecretException;
use MaxMessenger\Bot\MaxBot;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\Model\Response\Update;
use Psr\Http\Message\ServerRequestInterface;
use SensitiveParameter;
use SensitiveParameterValue;
use Throwable;
use TypeError;

use function count;
use function file_get_contents;
use function hash_equals;
use function str_contains;
use function strlen;

/**
 * Вспомогательные методы обработки событий и Webhook-запросов {@see MaxBot}.
 *
 * @internal
 */
trait MaxBotHelperTrait
{
    public const CONTENT_LENGTH_HEADER = 'Content-Length';
    public const CONTENT_LENGTH_SERVER_KEY = 'CONTENT_LENGTH';
    public const CONTENT_TYPE_HEADER = 'Content-Type';
    public const CONTENT_TYPE_SERVER_KEY = 'CONTENT_TYPE';
    public const REQUEST_METHOD_SERVER_KEY = 'REQUEST_METHOD';
    public const SECRET_HEADER = 'X-Max-Bot-Api-Secret';
    public const SECRET_SERVER_KEY = 'HTTP_X_MAX_BOT_API_SECRET';

    private ?CallbackHandler $callbackHandler = null;
    private ?CallbackJsonHandler $callbackJsonHandler = null;
    private ?CommandHandler $commandHandler = null;
    /**
     * @var array<class-string<BaseEvent>, list<Closure>>
     */
    private array $eventHandlers = [];
    /**
     * @var list<Closure(Throwable $exception, BaseEvent $event): (bool|void)>
     */
    private array $exceptionHandlers = [];
    /**
     * @var list<Closure(BaseEvent $event): (bool|void)>
     */
    private array $fallbackHandlers = [];
    /**
     * @var list<Closure(BaseEvent $event): (bool|void)>
     */
    private array $finalHandlers = [];
    /**
     * @var list<Closure(BaseEvent $event): (bool|void)>
     */
    private array $prepareHandlers = [];
    /**
     * @var SensitiveParameterValue<non-empty-string>|null
     */
    private ?SensitiveParameterValue $secret;
    /**
     * @var array<string, list<Closure(BaseEvent $event): (bool|void)>>
     */
    private array $typedHandlers = [];
    /**
     * @var (Closure(Update $update, Throwable $exception): void)|null
     */
    private ?Closure $updateErrorHandler = null;
    /**
     * @var (Closure(Update $update): (bool|Update))|null
     */
    private ?Closure $updateFilter;

    /**
     * Обрабатывает событие.
     *
     * @param BaseEvent $event Обрабатываемое событие.
     * @return bool `true`, если событие считается обработанным.
     */
    public function handleEvent(BaseEvent $event): bool
    {
        $event->handledIn = null;

        /** @psalm-suppress MixedArgumentTypeCoercion */
        $handled = self::handleEventUseHandlerList(
            $event,
            $this->prepareHandlers,
            HandlerListType::Prepare,
            null,
        ) || self::handleEventUseHandlerList(
            $event,
            $this->eventHandlers[$event::class] ?? [],
            HandlerListType::Event,
        ) || self::handleEventUseHandlerList(
            $event,
            $this->typedHandlers[$event->update->getUpdateTypeRaw()] ?? [],
            HandlerListType::Typed,
        ) || self::handleEventUseHandlerList(
            $event,
            $this->fallbackHandlers,
            HandlerListType::Fallback,
        );

        return self::handleEventUseHandlerList(
            $event,
            $this->finalHandlers,
            HandlerListType::Final,
            null,
        ) || $handled;
    }

    /**
     * Обрабатывает событие используя переданный список обработчиков.
     *
     * Если обработчик завершился без возвращаемого значения, событие по умолчанию считается обработанным.
     * Обход списка прекращается при статусе `true`.
     *
     * @param BaseEvent $event Обрабатываемое событие.
     * @param array<Closure(BaseEvent $event): (bool|void)> $handlers Обработчики события в порядке вызова.
     * @param HandlerListType $handlerListType Тип списка обработчиков.
     * @param bool|null $defaultIsHandled Статус при завершении обработчика без возвращаемого значения.
     * @return bool `true`, если один из обработчиков обработал событие.
     */
    public static function handleEventUseHandlerList(
        BaseEvent $event,
        array $handlers,
        HandlerListType $handlerListType = HandlerListType::Custom,
        ?bool $defaultIsHandled = true,
    ): bool {
        if (count($handlers) === 0) {
            return false;
        }

        $saveListType = $event->currentHandlerListType;

        try {
            foreach ($handlers as $handler) {
                $event->currentHandlerListType = $handlerListType;

                if ($event->handle($handler, $defaultIsHandled)) {
                    if ($event->handledIn === null && $handlerListType !== HandlerListType::Final) {
                        $event->handledIn = $handlerListType;
                    }

                    return true;
                }
            }

            return false;
        } finally {
            $event->currentHandlerListType = $saveListType;
        }
    }

    /**
     * Создаёт объект события бота на основе объекта обновления.
     *
     * @param Update $update Объект обновления.
     */
    public function makeEvent(Update $update): BaseEvent
    {
        return BaseEvent::new($update, $this->apiClient, $this->exceptionHandlers);
    }

    /**
     * Создаёт объект обновления из JSON-строки.
     *
     * @param string $string JSON-строка с данными обновления.
     */
    public static function makeUpdateFromString(string $string): Update
    {
        try {
            return Update::newFromJson($string);
        } catch (JsonException $e) {
            throw new BadRequestException('The body does not contain valid JSON.', $e);
        } catch (TypeError $e) {
            throw new BadRequestException($e->getMessage(), $e);
        }
    }

    /**
     * Читает тело PSR-7 запроса.
     *
     * @param ServerRequestInterface $request Запрос от Webhook.
     * @return non-empty-string Тело запроса.
     * @throws BadRequestException Если запрос не является корректным Webhook-запросом МАКС.
     * @throws InvalidSecretException Если секрет в запросе не совпадает с заданным.
     */
    public function readRequestContent(ServerRequestInterface $request): string
    {
        $contentLength = $request->getHeaderLine(self::CONTENT_LENGTH_HEADER);
        $this->validateRequestHeaders(
            $request->getMethod(),
            $request->getHeaderLine(self::CONTENT_TYPE_HEADER),
            $contentLength,
            $request->getHeaderLine(self::SECRET_HEADER),
        );

        $body = (string) $request->getBody();
        self::validateRequestBody($body, $contentLength);

        return $body;
    }

    /**
     * Читает тело запроса из глобального контекста.
     *
     * @return string Тело запроса
     */
    public function readRequestContentFromGlobal(): string
    {
        $contentLength = $_SERVER[self::CONTENT_LENGTH_SERVER_KEY] ?? '';
        $this->validateRequestHeaders(
            $_SERVER[self::REQUEST_METHOD_SERVER_KEY] ?? '',
            $_SERVER[self::CONTENT_TYPE_SERVER_KEY] ?? '',
            $contentLength,
            $_SERVER[self::SECRET_SERVER_KEY] ?? '',
        );

        $body = @file_get_contents('php://input');
        if ($body === false) {
            throw new BadRequestException('Failed to read request body.');
        }

        self::validateRequestBody($body, $contentLength);

        return $body;
    }

    /**
     * Проверяет тело входящего Webhook-запроса и совпадение его размера с Content-Length.
     *
     * @param string $body Тело запроса.
     * @param int|string $contentLength Длина тела в байтах, проверенная через validateRequestHeaders().
     * @throws BadRequestException Если тело пустое или его размер не совпадает с Content-Length.
     * @psalm-assert non-empty-string $body
     */
    public static function validateRequestBody(string $body, int|string $contentLength): void
    {
        if ($body === '') {
            throw new BadRequestException('Body is empty.');
        }

        if (strlen($body) !== (int) $contentLength) {
            throw new BadRequestException('The Body size does not match the passed content-length.');
        }
    }

    /**
     * Проверяет метод и заголовки входящего Webhook-запроса.
     *
     * @param string $method HTTP-метод запроса.
     * @param string $contentType Значение заголовка Content-Type или пустая строка, если он отсутствует.
     * @param int|string $contentLength Длина тела в байтах или пустая строка, если заголовок отсутствует.
     * @param string $requestSecret Секрет из запроса или пустая строка, если он отсутствует.
     * @throws BadRequestException Если метод, тип содержимого или длина содержимого некорректны.
     * @throws InvalidSecretException Если секрет в запросе не совпадает с заданным.
     */
    public function validateRequestHeaders(
        string $method,
        string $contentType,
        int|string $contentLength,
        #[SensitiveParameter]
        string $requestSecret,
    ): void {
        if ($method !== 'POST' || $contentLength === '') {
            throw new BadRequestException('Request method must be POST and Content-Length header is required.');
        }
        if (!str_contains($contentType, 'application/json')) {
            throw new BadRequestException('Content-Type must contain application/json.');
        }
        if ((int) $contentLength <= 0) {
            throw new BadRequestException('Content-Length must be greater than zero.');
        }
        $secret = $this->secret?->getValue();
        if ($secret !== null && !hash_equals($secret, $requestSecret)) {
            throw new InvalidSecretException();
        }
    }
}
