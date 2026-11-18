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
use SensitiveParameterValue;
use Throwable;

use function count;
use function file_get_contents;
use function hash_equals;
use function is_array;
use function json_decode;
use function preg_match;
use function str_contains;
use function strlen;

/**
 * Вспомогательные методы обработки событий и Webhook-запросов {@see MaxBot}.
 *
 * @internal
 */
trait MaxBotHelperTrait
{
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
     * @var (Closure(Update $update): bool)|null
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
            $data = json_decode($string, true, 16, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new BadRequestException('The body does not contain valid JSON.', $e);
        }

        if (!is_array($data)) {
            throw new BadRequestException('Data is not array.');
        }

        return Update::newFromData($data);
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
        $contentLength = $request->getHeaderLine('Content-Length');
        $this->validateRequestHeaders(
            $request->getMethod(),
            $request->getHeaderLine('Content-Type'),
            $contentLength,
            $request->getHeaderLine('X-Max-Bot-Api-Secret'),
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
        $contentLength = $_SERVER['CONTENT_LENGTH'] ?? '';
        $this->validateRequestHeaders(
            $_SERVER['REQUEST_METHOD'] ?? '',
            $_SERVER['CONTENT_TYPE'] ?? '',
            $contentLength,
            $_SERVER['HTTP_X_MAX_BOT_API_SECRET'] ?? '',
        );

        /** @psalm-suppress RiskyTruthyFalsyComparison */
        $body = @file_get_contents('php://input') ?: '';

        self::validateRequestBody($body, $contentLength);

        return $body;
    }

    /**
     * @psalm-assert non-empty-string $body
     */
    private static function validateRequestBody(string $body, string $contentLength): void
    {
        if ($body === '') {
            throw new BadRequestException('Body is empty.');
        }

        if (strlen($body) !== (int) $contentLength) {
            throw new BadRequestException('The Body size does not match the passed content-length.');
        }
    }

    private function validateRequestHeaders(
        string $method,
        string $contentType,
        string $contentLength,
        string $requestSecret,
    ): void {
        if ($method !== 'POST' || !str_contains($contentType, 'application/json') || $contentLength === '') {
            throw new BadRequestException('Required: POST, application/json, content-length');
        }

        if (preg_match('/\A[0-9]+\z/', $contentLength) !== 1) {
            throw new BadRequestException('Content-Length must be a non-negative integer.');
        }

        $secret = $this->secret?->getValue();
        if ($secret !== null && !hash_equals($secret, $requestSecret)) {
            throw new InvalidSecretException();
        }
    }
}
