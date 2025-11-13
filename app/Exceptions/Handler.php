<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;
use App\Exceptions\DomainException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnauthorizedException;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
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

    public function register(): void
    {
        //
    }

    public function render($request, Throwable $exception): SymfonyResponse
    {
        if ($exception instanceof DomainException || $exception instanceof ForbiddenException) {
            $status = match (true) {
                $exception instanceof NotFoundException => Response::HTTP_NOT_FOUND,
                $exception instanceof UnauthorizedException => Response::HTTP_UNAUTHORIZED,
                $exception instanceof ForbiddenException => Response::HTTP_FORBIDDEN,
                default => Response::HTTP_BAD_REQUEST,
            };

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => $exception->getMessage(),
                ], $status);
            }

            if ($request->header('X-Inertia')) {
                return back()->withErrors(['error' => $exception->getMessage()]);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', $exception->getMessage());
        }

        return parent::render($request, $exception);
    }
}
