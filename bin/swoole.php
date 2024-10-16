<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

use App\Middlewares\ExceptionHandleMiddleware;
use App\Middlewares\SwooleResponseEmitter;
use App\ServerRequest;
use Next\Http\Server\RequestHandler;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

date_default_timezone_set('PRC');
define('BASE_PATH', dirname(__DIR__) . '/');
require_once BASE_PATH . 'vendor/autoload.php';

$server          = new Server('0.0.0.0', 8989);
$routeDispatcher = require_once base_path('src/router.php');

$server->on('request', function (Request $request, Response $response) use ($routeDispatcher) {
    try {
        (new RequestHandler())
            ->withMiddleware(
                new SwooleResponseEmitter($response),
                new ExceptionHandleMiddleware(),
                $routeDispatcher
            )
            ->handle(ServerRequest::createFromSwooleRequest($request));
    } catch (\Throwable $e) {
        $response->end('Internal Server Error');
    }
});

$server->start();
