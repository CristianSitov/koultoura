<?php

namespace App\Exceptions;

use App\Support\Slack;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

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

    /*
     * Not worth a notification: a form filled in wrong, a stale tab, a page
     * that does not exist. They are ordinary, they are frequent, and a channel
     * that carries them is a channel nobody reads.
     */
    private const QUIET = [
        ValidationException::class,
        AuthenticationException::class,
        TokenMismatchException::class,
        ModelNotFoundException::class,
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
            if ($this->isQuiet($e)) {
                return;
            }

            /*
             * Keyed on where and what, so a fault that fires on every request
             * says so once rather than filling the channel — and a second,
             * different fault still gets through while the first is quiet.
             */
            Slack::throttled(
                get_class($e).':'.$e->getFile().':'.$e->getLine(),
                'Something broke',
                [
                    'What' => class_basename($e).' — '.$e->getMessage(),
                    'Where' => str_replace(base_path().'/', '', $e->getFile()).':'.$e->getLine(),
                    'Page' => Request::method().' '.Request::fullUrl(),
                ]
            );
        });
    }

    /** Ordinary, frequent, and nothing anybody needs to be told about. */
    private function isQuiet(Throwable $e): bool
    {
        foreach (self::QUIET as $quiet) {
            if ($e instanceof $quiet) {
                return true;
            }
        }

        // A 404 or a 419 is the web being the web; a 500 is ours.
        return $e instanceof HttpExceptionInterface && $e->getStatusCode() < 500;
    }
}
