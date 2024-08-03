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


$pdo = new Connection();
$connection = $pdo->connect();
$urlRepo = new UrlRepository($connection);
$checksRepo = new UrlChecker($connection);

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


    $checks = $checksRepo->allByUrlId($args['id']);
    if ($checks) {
        $sortedChecks = Arr::sortDesc($checks, function ($value) {
            return $value['created_at'];
        });
    } else {
        $sortedChecks = null;
    }

    if (isset($urlRepo->findById($args['id'])[0])) {
        $urlData = $urlRepo->findById($args['id'])[0];
    }

    $params = [
        'urlData' => $urlData ?? null,
        'errors' => [],
        'flash' => $messages ?? [],
        'checks' => $sortedChecks
    ];

    return $this->get('view')->render($response, 'url.html.twig', $params);
})->setName('url.show');

$app->get('/urls', function ($request, $response) use ($urlRepo, $checksRepo) {
    $urls = $urlRepo->all();
    if($urls) {
        $sortedUrls = Arr::sortDesc($urls, function ($value) {
            return $value['created_at'];
        });
        $urlsPreparedForPage = Arr::map($sortedUrls, function ($value) use ($checksRepo) {
            $lastUrlCheck = $checksRepo->lastByUrlId($value['id']);
            $value['createdAt'] = $lastUrlCheck[0]['created_at'] ?? null;
            $value['statusCode'] = $lastUrlCheck[0]['status_code'] ?? null;
            return $value;
        });
    }
    $data = [
        'urls' => $urlsPreparedForPage ?? null
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

    $existingUrl = null;
    // if url exits, redirect to existing id
    if($normalizedUrl) {
        $existingUrl = $urlRepo->findByName($normalizedUrl);
    }

    if ($existingUrl) {
        $redirectId = $existingUrl[0]['id'];
        $args['id'] = $redirectId;
        $this->get('flash')->addMessage('success', 'Страница уже существует');
        return $response->withRedirect($router->urlFor('url.show', $args));
    }

    if($normalizedUrl) {
        $created = Carbon::now()->toDateTimeString();
        $redirectId = $urlRepo->insert($normalizedUrl, $created);
        if($redirectId) {
            $args['id'] = $redirectId;
        } else {
            $args['id'] = '1';
        }

        $this->get('flash')->addMessage('success', 'Страница успешно добавлена');
        return $response->withRedirect($router->urlFor('url.show', $args));
    }


})->setName('url.add');

$app->post('/urls/{url_id}/checks', function ($request, $response, $args) use ($router, $checksRepo, $urlRepo) {
    $createdAt = Carbon::now()->toDateTimeString();
    $url_id = $args['url_id'];
    $responseData = null;
    if(isset($urlRepo->findById($url_id)[0]['name'])) {
        $urlName = $urlRepo->findById($url_id)[0]['name'];
        $responseData = $checksRepo->getUrlResponse($urlName);
        if (isset($responseData['flash']['type'])) {
            $type = $responseData['flash']['type'];
            $text = $responseData['flash']['text'];
            $this->get('flash')->addMessage($type, $text);
        }
        $statusCode = $responseData['statusCode'];
        $documentData = [];
        if (isset($responseData['flash']['type']) && $responseData['flash']['type'] === 'success') {
            $documentData = $checksRepo->getDocumentData($urlName);
        }
        //        file_put_contents('debug.log', print_r($documentData, true), FILE_APPEND);
        $title = $documentData['title'] ?? '';
        $h1 = $documentData['h1'] ?? '';
        $description = $documentData['description'] ?? '';

        if ($responseData['flash']['type'] !== 'danger') {
            $checksRepo->insert($url_id, $statusCode, $h1, $title, $description, $createdAt);
        }
    }


    $args['id'] = $args['url_id'];
    return $response->withRedirect($router->urlFor('url.show', $args));

})->setName('checks.add');


$app->run();