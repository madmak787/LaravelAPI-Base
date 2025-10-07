<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\PersonalAccessToken;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        /**
         * Sanctum & middleware aliases
         * You can now use `abilities:` or `auth:sanctum` directly in routes.
         */
        $middleware->alias([
            'abilities' => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
            'ability' => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
        ]);

        /**
         * Optional: If you want to redirect guests for web routes.
         * Not needed for APIs — so we skip `redirectGuestsTo()`
         */
    })
    ->withExceptions(function (Exceptions $exceptions) {
        /**
         * Return JSON consistently for all authentication failures.
         */
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                $authHeader = $request->header('Authorization');

                if (! $authHeader) {
                    return response()->json(['message' => 'Token not provided.'], 401);
                }

                $token = str_replace('Bearer ', '', $authHeader);

                if (! $token) {
                    return response()->json(['message' => 'Invalid Authorization header.'], 401);
                }

                $accessToken = PersonalAccessToken::findToken($token);

                if (! $accessToken) {
                    return response()->json(['message' => 'Token is invalid or expired.'], 401);
                }

                if (! $accessToken->tokenable) {
                    return response()->json(['message' => 'User not found or unauthorized.'], 401);
                }

                return response()->json(['message' => 'Authentication failed.'], 401);
            }

            // For non-JSON requests (e.g. web login page)
            return redirect()->guest(route('login'));
        });
    })
    ->create();
