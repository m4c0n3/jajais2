<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureModuleActive;
use App\Http\Middleware\RequestIdMiddleware;
use App\Http\Middleware\SetUserLocale;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        __DIR__.'/../app/Console/Commands',
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'module.active' => EnsureModuleActive::class,
            'permission' => \Spatie\Permission\Middlewares\PermissionMiddleware::class,
            'role' => \Spatie\Permission\Middlewares\RoleMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class,
        ]);

        $middleware->appendToGroup('web', RequestIdMiddleware::class);
        $middleware->appendToGroup('web', SetUserLocale::class);
        $middleware->appendToGroup('api', RequestIdMiddleware::class);
        $middleware->appendToGroup('api', SetUserLocale::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
