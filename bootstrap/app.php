<?php

use App\Exceptions\ProjectException;
use App\Exceptions\TaskException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handle custom Task exceptions globally
        $exceptions->render(function (TaskException $e, Request $request) {
            Log::error('Global TaskException', [
                'message' => $e->getMessage(),
                'code'    => $e->getCode(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(
                    ['error' => $e->getMessage()],
                    $e->getCode() ?: 500
                );
            }

            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        });

        // Handle custom Project exceptions globally
        $exceptions->render(function (ProjectException $e, Request $request) {
            Log::error('Global ProjectException', [
                'message' => $e->getMessage(),
                'code'    => $e->getCode(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(
                    ['error' => $e->getMessage()],
                    $e->getCode() ?: 500
                );
            }

            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        });

        // Handle 404s
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Resource not found.'], 404);
            }
        });
    })->create();
