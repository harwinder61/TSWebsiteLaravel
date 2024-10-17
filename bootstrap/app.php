<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Modules\Auth\app\Http\Middleware\AuthMiddleware;
use Modules\Escort\app\Http\Middleware\AuthEscort;
use Modules\Fan\app\Http\Middleware\AuthFan;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
        $middleware->alias([
            'jwt_auth' => AuthMiddleware::class,
            'auth_escort' => AuthEscort::class,
            'auth_fan' => AuthFan::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
