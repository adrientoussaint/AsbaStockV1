<?php

// DIC configuration
$container = $app->getContainer();
// view renderer
// $container['renderer'] = function ($c) {
//     $settings = $c->get('settings')['renderer'];
//     return new Slim\Views\Twig($settings['template_path']);
// };
$container['environment'] = function () {
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $_SERVER['SCRIPT_NAME'] = dirname(dirname($scriptName)) . '/' . basename($scriptName);
    return new Slim\Http\Environment($_SERVER);
};
$container['auth']= function($container){
    return new \App\Auth($container);
};

$container['view'] = function($c){
    $view = new Slim\Views\Twig(__DIR__ . '/../templates', [
        'cache' => false,
    ]);
    $view->addExtension(new \Slim\Views\TwigExtension(
        $c->router,
        $c->request->getUri()
    ));

    $view->getEnvironment()->addGlobal('auth', [
        'user' => $c->auth->user(),
    ]);

    return $view;
};

$container['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        return $response->withRedirect($c->router->pathFor('home'));
    };
};

$container['logger'] = function ($c) {
    $settings = $c->get('settings');
    $logger = new Monolog\Logger($settings['logger']['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['logger']['path'], Monolog\Logger::DEBUG));
    return $logger;
};

$container['AuthController']= function($container){
    return new \App\Controller\Auth\AuthController($container);
};


// $app->add(new App\Middleware\AuthMiddleware($container));

// Bootstrap Eloquent ORM
$dbconfig = $container->get('settings')['database'];
$connFactory = new \Illuminate\Database\Connectors\ConnectionFactory(new Illuminate\Container\Container);
$conn = $connFactory->make($dbconfig);
$resolver = new \Illuminate\Database\ConnectionResolver();
$resolver->addConnection('default', $conn);
$resolver->setDefaultConnection('default');
\Illuminate\Database\Eloquent\Model::setConnectionResolver($resolver);
