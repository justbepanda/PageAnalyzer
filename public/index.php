<?php

namespace Hexlet\Code;

require __DIR__ . '/../vendor/autoload.php';

use DI\Container;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

session_start();

// Create Container
$container = new Container();
AppFactory::setContainer($container);

$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});
$container->set('view', function () {
    return Twig::create(__DIR__ . '/../templates');
});
$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});

$app = AppFactory::create();

// Add Twig-View Middleware
$app->add(TwigMiddleware::createFromContainer($app));



// Show errors
$app->addErrorMiddleware(true, true, true);

// Define named route
$app->get('/', function ($request, $response, $args) {
    return $this->get('view')->render($response, 'index.html.twig');
})->setName('profile');

$app->run();