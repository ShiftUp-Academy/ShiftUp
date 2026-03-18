<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\LogRequestResponse::class);
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);
        $middleware->alias([
            'moderator' => \App\Http\Middleware\CheckModeratorPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($e->getCode() >= 500 || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                \Illuminate\Support\Facades\Log::critical('EXCEPTION-CAUGHT: ' . $e->getMessage(), [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'input' => $request->except(['password']),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        });
    })->create();
