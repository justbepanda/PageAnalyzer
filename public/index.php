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
use Valitron\Validator;

Validator::langDir(__DIR__ . '/../validator_lang'); // always set langDir before lang.
Validator::lang('ru');

session_start();

$urlRepo = null;
$checksRepo = null;

try {
    $pdo = new Connection();
    if ($pdo) {
        $urlRepo = new UrlRepository($pdo);
        $checksRepo = new UrlChecker($pdo);
    }

} catch (\PDOException|\Exception $e) {
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
    $params = [
        'flash' => $flash
    ];
    return $this->get('view')->render($response, 'index.html.twig', $params);
})->setName('home');


$app->get('/urls/{id}', function ($request, $response, $args) use ($urlRepo, $checksRepo) {
    $messages = $this->get('flash')->getMessages();

    $urlData = '';
    $sortedChecks = [];
    if($checksRepo && $urlRepo) {
        $checks = $checksRepo->allByUrlId($args['id']);
        if ($checks) {
            $sortedChecks = Arr::sortDesc($checks, function ($value) {
                return $value['created_at'];
            });
        } else {
            $sortedChecks = null;
        }
        $urlData = $urlRepo->findById($args['id'])[0];
    }
    $params = [
        'urlData' => $urlData,
        'errors' => [],
        'flash' => $messages ?? [],
        'checks' => $sortedChecks
    ];

    return $this->get('view')->render($response, 'url.html.twig', $params);
})->setName('url.show');

$app->get('/urls', function ($request, $response) use ($urlRepo, $checksRepo) {
    $urls = $urlRepo->all();
    $sortedUrls = Arr::sortDesc($urls, function ($value) {
        return $value['created_at'];
    });

    $urlsPreparedForPage = Arr::map($sortedUrls, function ($value) use ($checksRepo) {
        $lastUrlCheck = $checksRepo->lastByUrlId($value['id']);
        $value['createdAt'] = $lastUrlCheck[0]['created_at'] ?? null;
        $value['statusCode'] = $lastUrlCheck[0]['status_code'] ?? null;
        return $value;
    });
    $data = [
        'urls' => $urlsPreparedForPage
    ];

    return $this->get('view')->render($response, 'urls.html.twig', $data);
})->setName('urls.show');

$app->post('/urls', function ($request, $response) use ($router, $urlRepo) {
    $data = $request->getParsedBody();
    $url = $data['url']['name'];
    $validator = new Validator(['URL' => $url]);
    $validator->setPrependLabels(false);

    $validator->rules([
        'required' => [
            ['URL']
        ],
        'url' => [
            ['URL']
        ],
        'lengthMax' => [
            ['URL', 255]
        ],
    ]);

    if (!$validator->validate()) {
        // Errors
        $errors = $validator->errors();
        $params = [];
        if ($errors && isset($errors['URL'])) {
            $params = [
                'errors' => $errors['URL'],
                'url' => $url
            ];
        }

        return $this->get('view')->render($response->withStatus(422), 'index.html.twig', $params);
    }

    $normalizedUrl = $urlRepo->normalize($url);

    // if url exits, redirect to existing id
    $existingUrl = $urlRepo->findByName($normalizedUrl);
    if ($existingUrl) {
        $redirectId = $existingUrl[0]['id'];
        $args['id'] = $redirectId;
        $this->get('flash')->addMessage('success', 'Страница уже существует');
        return $response->withRedirect($router->urlFor('url.show', $args));
    }

    $created = Carbon::now()->toDateTimeString();
    $redirectId = $urlRepo->insert($normalizedUrl, $created);
    $args['id'] = $redirectId;
    $this->get('flash')->addMessage('success', 'Страница успешно добавлена');
    return $response->withRedirect($router->urlFor('url.show', $args));
})->setName('url.add');

$app->post('/urls/{url_id}/checks', function ($request, $response, $args) use ($router, $checksRepo, $urlRepo) {
    $createdAt = Carbon::now()->toDateTimeString();
    $url_id = $args['url_id'];


    $urlName = $urlRepo->findById($url_id)[0]['name'];
    $responseData = $checksRepo->getUrlResponse($urlName);
    $statusCode = $responseData['statusCode'];
    $documentData = [];
    if ($responseData['flash']['type'] === 'success') {
        $documentData = $checksRepo->getDocumentData($urlName);
    }

//        file_put_contents('debug.log', print_r($documentData, true), FILE_APPEND);
    $title = $documentData['title'] ?? '';
    $h1 = $documentData['h1'] ?? '';
    $description = $documentData['description'] ?? '';

    if ($responseData['flash']['type'] !== 'danger') {
        $checksRepo->insert($url_id, $statusCode, $h1, $title, $description, $createdAt);
    }

    $this->get('flash')->addMessage($responseData['flash']['type'], $responseData['flash']['text']);
    $args['id'] = $args['url_id'];
    return $response->withRedirect($router->urlFor('url.show', $args));

})->setName('checks.add');


$app->run();