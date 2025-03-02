<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

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

    public function render($request, Throwable $throwable)
    {
        if (get_class($throwable) === "Illuminate\Validation\ValidationException") {
            $status = 422;
        } else {
            if ($throwable->getCode() === 0) {
                $status = 500;
            } else {
                $status = $throwable->getCode();
            }
        }

        if (get_class($throwable) === "App\Exceptions\MyCustomException") {
            return response()->json([
                'error' => $throwable->getMessage(),
                'details' => $throwable->getDetails()
            ], $status);
        }
        else {
            return response()->json([
                'error' => $throwable->getMessage(),
            ], $status);
        }
    }

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
