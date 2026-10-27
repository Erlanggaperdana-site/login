<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Titik awal aplikasi: mendaftarkan route, middleware, dan penanganan error.
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Tamu yang membuka halaman terkunci diarahkan ke halaman login.
        $middleware->redirectGuestsTo('/login');
        // Mendaftarkan alias "role" agar route dapat menulis ->middleware('role:admin').
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Penanganan error khusus belum diperlukan; halaman 403 diatur pada resources/views/errors.
    })->create();
