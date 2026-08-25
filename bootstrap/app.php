<?php

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // A restrict-on-delete FK violation (deleting a discount/tax-rate/etc
        // that's still referenced by order history) is an expected, user-facing
        // outcome here, not a server bug — see "Delete vs. deactivate" in the
        // brothrrs-cafe-backend skill. Report it as 409, not a raw 500.
        $exceptions->render(function (QueryException $e, Request $request) {
            if (($request->is('api/*') || $request->expectsJson()) && $e->getCode() === '23000') {
                return response()->json([
                    'message' => 'This record is still referenced by other data and cannot be deleted.',
                ], 409);
            }
        });
    })->create();
