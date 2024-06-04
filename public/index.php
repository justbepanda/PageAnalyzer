<?php

namespace Hexlet\Code;

require __DIR__ . '/../vendor/autoload.php';

use DI\Container;
use Illuminate\Support\Arr;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Slim\Flash\Messages;
use Carbon\Carbon;

session_start();

try {
    $pdo = Connection::get()->connect();
    $urlRepo = new UrlRepository($pdo);
} catch (\PDOException $e) {
    echo $e->getMessage();
}

// Create Container
$container = new Container();
AppFactory::setContainer($container);

// Configure Twig renderer
$container->set('view', function () {
    return Twig::create(__DIR__ . '/../templates', ['cache' => false]);
});

// Configure Flash messages
$container->set('flash', function () {
    return new Messages();
});

$app = AppFactory::create();

// Add Twig-View Middleware
$app->add(TwigMiddleware::createFromContainer($app));

// Show errors
$app->addErrorMiddleware(true, true, true);

$router = $app->getRouteCollector()->getRouteParser();


// Define named route
$app->get('/', function ($request, $response) {
    $flash = $this->get('flash')->getMessages();
//    dump($flash);
    $params = [
        'flash' => $flash
    ];
    return $this->get('view')->render($response, 'index.html.twig', $params);
})->setName('home');

$app->get('/urls', function ($request, $response) use ($urlRepo) {
    $urls = $urlRepo->all();
    $sortedUrls = Arr::sortDesc($urls, function ($value) {
        return $value['created_at'];
    });
    $data = [
        'urls' => $sortedUrls
    ];
//    file_put_contents('debug.log', print_r($data, true), FILE_APPEND);
    return $this->get('view')->render($response, 'urls.html.twig', $data);
})->setName('urls.show');

$app->get('/urls/{id}', function ($request, $response, $args) use ($urlRepo) {
    $messages = $this->get('flash')->getMessages();

    $params = [
        'urlData' => $urlRepo->findById($args['id'])[0],
        'errors' => [],
        'flash' => $messages ?? []
    ];

    return $this->get('view')->render($response, 'url.html.twig', $params);
})->setName('url.show');

$app->post('/urls', function ($request, $response) use ($router, $urlRepo) {
    $data = $request->getParsedBody();
    $url = $data['url']['name'];

    $validator = new UrlValidator();
    $normalizedUrl = $validator->normalize($url);

    // if url exits, redirect to exits id
    $existingUrl = $urlRepo->findByName($normalizedUrl);
    if ($existingUrl) {
        $redirectId = $existingUrl[0]['id'];
        $args['id'] = $redirectId;
        $this->get('flash')->addMessage('success', 'Страница уже существует');
        return $response->withRedirect($router->urlFor('url.show', $args));
    }

    $errors = $validator->validate($url);

    // if no error, add new url
    if (empty($errors)) {
        $created = Carbon::now()->toDateTimeString();
        $redirectId = $urlRepo->insert($url, $created);
        $args['id'] = $redirectId;
        $this->get('flash')->addMessage('success', 'Страница успешно добавлена');
        return $response->withRedirect($router->urlFor('url.show', $args));
    }


    // if errors, show them
    $params = [
        'errors' => $errors,
        'url' => $url
    ];

    return $this->get('view')->render($response->withStatus(422), 'index.html.twig', $params);

})->setName('url.add');


$app->run();