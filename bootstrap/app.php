<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\IsAdmin::class,
            'super_admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
            'customer' => \App\Http\Middleware\EnsureCustomer::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // A request bigger than post_max_size is thrown away whole by PHP, so
        // no fields and no files arrive. Left alone the admin gets a bare 413
        // and no hint that it was the images, not the prices, that were too big.
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if ($e->getStatusCode() !== 413) {
                return null;
            }

            $perFile = ini_get('upload_max_filesize') ?: '2M';
            $perRequest = ini_get('post_max_size') ?: '8M';

            $message = "The images were too large to upload, so nothing was saved. "
                ."This server accepts at most {$perFile} per image and {$perRequest} for the whole submission. "
                .'Remove or compress an image and try again, or ask the host to raise post_max_size and upload_max_filesize in php.ini.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 413);
            }

            return back()
                ->withInput()
                ->withErrors(['images' => $message]);
        });
    })->create();
