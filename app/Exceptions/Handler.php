<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Convert an authentication exception into a response.
     * For REST API, always return JSON with 401 status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return response()->json([
            'message' => 'Unauthenticated.',
        ], 401);
    }

    /**
     * Render an exception into an HTTP response.
     * For REST API, always return JSON responses.
     *
     * @param  Request  $request
     * @param  Throwable  $e
     * @return Response
     * @throws Throwable
     */
    public function render($request, Throwable $e)
    {
        // Always handle as API exception for REST API (always return JSON)
        return $this->handleApiException($request, $e);
    }

    /**
     * Handle API exceptions and return JSON response.
     * Exceptions are handled in order of specificity: JWT > Validation > Auth > Authorization > Not Found > Other
     *
     * @param  Request  $request
     * @param  Throwable  $e
     * @return Response
     */
    private function handleApiException(Request $request, Throwable $e): Response
    {
        // JWT exceptions MUST be handled first (before AuthenticationException)
        // to prevent 500 errors when JWT middleware throws exceptions
        if ($e instanceof TokenExpiredException) {
            return response()->json([
                'message' => 'Token has expired.',
            ], 401);
        }

        if ($e instanceof TokenInvalidException) {
            return response()->json([
                'message' => 'Token is invalid.',
            ], 401);
        }

        if ($e instanceof TokenBlacklistedException) {
            return response()->json([
                'message' => 'Token has been blacklisted.',
            ], 401);
        }

        if ($e instanceof JWTException) {
            return response()->json([
                'message' => 'Could not parse token.',
            ], 401);
        }

        // Validation exceptions - return 422 with errors
        if ($e instanceof ValidationException) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors(),
            ], 422);
        }

        // AuthenticationException (thrown by auth:api middleware when token is missing/invalid)
        if ($e instanceof AuthenticationException) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // AuthorizationException (403 - thrown by Gates/Policies)
        if ($e instanceof AuthorizationException) {
            return response()->json([
                'message' => 'This action is unauthorized.',
            ], 403);
        }

        // ModelNotFoundException (404 - thrown by findOrFail)
        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'message' => 'Resource not found.',
            ], 404);
        }

        // NotFoundHttpException (404 - route not found)
        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'message' => 'Resource not found.',
            ], 404);
        }

        // MethodNotAllowedHttpException (405)
        if ($e instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'message' => 'Method not allowed.',
            ], 405);
        }

        // For all other exceptions, return appropriate status code
        // If exception has getStatusCode method, use it; otherwise default to 500
        $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
        $message = $statusCode === 500 ? 'Internal server error.' : ($e->getMessage() ?: 'An error occurred.');

        return response()->json([
            'message' => $message,
        ], $statusCode);
    }
}
