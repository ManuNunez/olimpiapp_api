<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register custom middleware aliases
        $middleware->alias([
            'admin' => \App\Http\Middleware\Admin::class,
            'student' => \App\Http\Middleware\Student::class,
            'role' => \App\Http\Middleware\RoleChecker::class,
            'contest.access' => \App\Http\Middleware\ContestAccess::class,
            'contest.time' => \App\Http\Middleware\ContestTimeWindow::class,
            'resource.owner' => \App\Http\Middleware\ResourceOwner::class,
        ]);
        
        // Register middleware for API routes
        $middleware->api([
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:60,1',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
