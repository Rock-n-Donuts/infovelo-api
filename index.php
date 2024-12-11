<?php

require __DIR__ . '/vendor/autoload.php';

use Rockndonuts\Hackqc\Controllers\APIController;
use Rockndonuts\Hackqc\Controllers\UserController;
use Rockndonuts\Hackqc\Controllers\ContributionController;
use Rockndonuts\Hackqc\Http\Response;
use Rockndonuts\Hackqc\Logger;
use Rockndonuts\Hackqc\Middleware\AuthMiddleware;
use Rockndonuts\Hackqc\Models\DB;

const APP_PATH = __DIR__;

if (!file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
} else {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../'); // server, set file out of webroot
}
$dotenv->load();

define("CUSTOM_LOGS", $_ENV['CUSTOM_LOGS'] ?? false);
error_reporting(E_ERROR);

/**
 * @todo refactor
 */
header("Access-Control-Allow-Origin: *");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        // may also be using PUT, PATCH, HEAD etc
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}

$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {

    $controller = new APIController();
    $userController = new UserController();
    $contributionController = new ContributionController();

    $r->addRoute('POST', '/auth', [$userController, 'createUser']);

    $r->addRoute('POST', '/contribution/{id:\d+}/reply', [$contributionController, 'reply']);
    $r->addRoute('POST', '/contribution/{id:\d+}/vote', [$contributionController, 'vote']);
    $r->addRoute('GET', '/contribution/{id:\d+}', [$contributionController, 'getUserVoteStatus']);
    $r->addRoute('POST', '/contribution', [$contributionController, 'createContribution']);

    $r->addRoute('GET', '/update', [$controller, 'updateData']);
    $r->addRoute('POST', '/import', [$contributionController, 'import']);
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        (new Response(['error' => 'Not found'], 404))->send();
        exit;
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        (new Response(['error' => 'Not allowed'], 405))->send();
        exit;
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        if ($handler[1] === 'import' || $handler[1] === 'get') {
            call_user_func_array($handler, $vars);
            break;
        }

        if ($handler[1] !== 'createUser' && $handler[1] !== 'validateGeobase') {
            try {
                AuthMiddleware::auth();
            } catch (\JsonException $e) {
                AuthMiddleware::unauthorized();
                die;
            }
        }

        call_user_func_array($handler, $vars);
        break;
}