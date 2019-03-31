<?php

require __DIR__.'/../vendor/autoload.php';

$_ENV['APP_CONFIG_CACHE'] = __DIR__.'/../cache/config.php';
$_ENV['APP_SERVICES_CACHE'] = __DIR__.'/../cache/services.php';
$_ENV['APP_PACKAGES_CACHE'] = __DIR__.'/../cache/packages.php';
$_ENV['APP_ROUTES_CACHE'] = __DIR__.'/../cache/routes.php';

$app = new Illuminate\Foundation\Application(
   dirname(__DIR__)
);

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    Illuminate\Foundation\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    Illuminate\Foundation\Exceptions\Handler::class
);

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

require_once(__DIR__.'/../routes.php');

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
