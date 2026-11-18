<?php

declare(strict_types=1);

/**
 * Маршрутизатор встроенного веб-сервера PHP для тестов HTTP-клиентов.
 *
 * - `/status/{code}` — ответ с заданным кодом;
 * - `/delay/{ms}` — ответ после паузы;
 * - `/headers` — ответ с повторяющимся заголовком и пробелами вокруг значения;
 * - `/head` — ресурс с ненулевым `Content-Length`;
 * - `/redirect` — перенаправление на `/headers` с промежуточными заголовками;
 * - `/no-content-type` — ответ без заголовка `Content-Type`;
 * - остальные адреса возвращают описание запроса в JSON.
 */

$path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

if ($path === '/head') {
    header('Content-Type: text/plain');
    header('Content-Length: 5');
    echo 'hello';

    return true;
}

if ($path === '/redirect') {
    header('Location: /headers', true, 302);
    header('Content-Type: text/plain');
    header('X-Intermediate: redirect');

    return true;
}

if (preg_match('~^/delay/(\d+)$~', $path, $matches) === 1) {
    usleep((int) $matches[1] * 1000);
}

if (preg_match('~^/status/(\d{3})$~', $path, $matches) === 1) {
    http_response_code((int) $matches[1]);
}

if ($path === '/headers') {
    header('X-Multi: first', false);
    header('X-Multi: second', false);
    header('X-Spaced:   value  ');
}

if ($path === '/no-content-type') {
    ini_set('default_mimetype', '');
    echo 'plain';

    return true;
}

header('Content-Type: application/json');

echo json_encode([
    'method' => $_SERVER['REQUEST_METHOD'] ?? null,
    'uri' => $_SERVER['REQUEST_URI'] ?? null,
    'headers' => getallheaders(),
    'body' => file_get_contents('php://input'),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

return true;
