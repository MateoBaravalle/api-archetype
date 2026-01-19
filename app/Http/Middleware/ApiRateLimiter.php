<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimiter
{
    /**
     * The rate limiter managed by this middleware.
     */
    protected RateLimiter $limiter;

    /**
     * Create a new middleware instance.
     */
    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->getRateLimiterKey($request);

        if ($this->limiter->tooManyAttempts($key, $this->getMaxAttempts())) {
            return $this->buildResponse($key);
        }

        $this->limiter->hit($key, $this->getDecayMinutes() * 60);

        $response = $next($request);

        return $this->addHeaders(
            $response,
            $this->getMaxAttempts(),
            $this->calculateRemainingAttempts($key),
            $this->getRetryAfter($key)
        );
    }

    /**
     * Get the rate limiter key for the request.
     */
    protected function getRateLimiterKey(Request $request): string
    {
        return $request->user()
            ? 'api:'.$request->user()->id
            : 'api:'.sha1($request->ip());
    }

    /**
     * Get the maximum number of attempts for the rate limiter.
     */
    protected function getMaxAttempts(): int
    {
        return 60; // 60 attempts per minute
    }

    /**
     * Get the number of minutes to decay the rate limiter.
     */
    protected function getDecayMinutes(): int
    {
        return 1; // Time window in minutes
    }

    /**
     * Calculate the number of remaining attempts.
     */
    protected function calculateRemainingAttempts(string $key): int
    {
        return $this->limiter->remaining($key, $this->getMaxAttempts());
    }

    /**
     * Get the number of seconds until the next retry.
     */
    protected function getRetryAfter(string $key): int
    {
        return $this->limiter->availableIn($key);
    }

    /**
     * Build the response for when the rate limit is exceeded.
     */
    protected function buildResponse(string $key): Response
    {
        $response = response()->json([
            'success' => false,
            'message' => 'Too many requests',
            'errors' => [
                'rate_limit' => 'You have exceeded the limit of allowed requests'
            ]
        ], 429);

        return $this->addHeaders(
            $response,
            $this->getMaxAttempts(),
            $this->calculateRemainingAttempts($key),
            $this->getRetryAfter($key)
        );
    }

    /**
     * Add the rate limit headers to the response.
     */
    protected function addHeaders(Response $response, int $maxAttempts, int $remainingAttempts, int $retryAfter): Response
    {
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remainingAttempts,
            'X-RateLimit-Reset' => $retryAfter,
        ]);

        return $response;
    }
}
