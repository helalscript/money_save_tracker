<?php

use App\Helpers\Helper;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withBroadcasting(
        __DIR__ . '/../routes/channels.php',
        ['prefix' => 'api', 'middleware' => ['auth:api']],
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
        $middleware->alias([
            'role_check' => \App\Http\Middleware\RoleCheckMiddleWare::class,
            'is_admin' => \App\Http\Middleware\IsAdmin::class,
            'is_user' => \App\Http\Middleware\IsUser::class,
        ]);
    })
    ->withExceptions(function ($exceptions) {
        $exceptions->render(function (Throwable $e, $request) {

            if ($request->is('api/*')) {

                // Validation
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return Helper::jsonErrorResponse($e->getMessage(), 422, $e->errors());
                }

                // Model not found
                if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    return Helper::jsonErrorResponse($e->getMessage(), 404);
                }

                // Auth
                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    return Helper::jsonErrorResponse($e->getMessage(), 401);
                }

                // Authorization
                if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                    return Helper::jsonErrorResponse($e->getMessage(), 403);
                }

                // Rate limit
                if ($e instanceof TooManyRequestsHttpException) {
                    return Helper::jsonErrorResponse(
                        'Too many attempts. Please try again later.',
                        429,
                        ['retry_after' => $e->getHeaders()['Retry-After'] ?? 60]
                    );
                }

                // Default
                $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

                return Helper::jsonErrorResponse($e->getMessage(), $statusCode);
            }

            // fallback web / default
            // return $e;
        });
    })
    ->create();
