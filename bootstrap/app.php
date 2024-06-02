<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__))
  ->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',
    commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
    health: '/up',
    then: function () {
      Route::middleware('web')
        ->group(base_path('routes/web/admin/settings.php'));
      Route::middleware('web')
        ->group(base_path('routes/web/admin/users.php'));
      Route::middleware('web')
        ->group(base_path('routes/web/authentication.php'));
      Route::middleware('web')
        ->group(base_path('routes/jetstream.php'));
      Route::middleware('web')
        ->group(base_path('routes/web/dashboard/dashboard.php'));
    }
  )
  ->withMiddleware(function (Middleware $middleware) {
    // Add middleware configurations here if needed
  })
  ->withExceptions(function (Exceptions $exceptions) {
    // Add exception handling configurations here if needed
  })
  ->create();
