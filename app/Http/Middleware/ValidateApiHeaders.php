<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->validateAcceptHeader($request)) {
            return response()->json([
                'success' => false,
                'message' => 'El header Accept debe ser application/json'
            ], 400);
        }

        if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH')) {
            if (!$this->validateContentTypeHeader($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El header Content-Type debe ser application/json'
                ], 400);
            }
        }

        return $next($request);
    }

    /**
     * Validate the Accept header.
     */
    protected function validateAcceptHeader(Request $request): bool
    {
        return $request->hasHeader('Accept') &&
               $request->header('Accept') === 'application/json';
    }

    /**
     * Validate the Content-Type header.
     */
    protected function validateContentTypeHeader(Request $request): bool
    {
        return $request->hasHeader('Content-Type') &&
               $request->header('Content-Type') === 'application/json';
    }
}
