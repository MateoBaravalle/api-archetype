<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Resources\ApiCollection;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Session\TokenMismatchException;
use JsonSerializable;

/**
 * Trait ApiResponseFormatter
 *
 * Provides methods to format and handle API responses,
 * including exception handling and resource transformation.
 */
trait ApiResponseFormatter
{
    /**
     * Formats an error response
     */
    protected function errorResponse(string $message, int $status = 500, array $errors = []): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return $this->jsonResponseWithPreserveDecimal($response, $status);
    }
    /**
     * Formats a success response
     */
    protected function successResponse($data, string $message = 'Operation successful', int $status = 200): JsonResponse
    {
        // 1. If it's a Paginator, apply collection transformation
        if ($data instanceof LengthAwarePaginator) {
            $data = $this->transformCollection($data);
        }
        // 2. If it's a Model, apply resource transformation
        elseif ($data instanceof Model) {
            $data = $this->transformResource($data);
        }
        // 3. Basic allowed types (null, bool, array), everything else throws an exception (fail fast)
        elseif ($data !== null && !is_array($data) && !is_bool($data)) {
            $type = is_object($data) ? get_class($data) : gettype($data);
            throw new \InvalidArgumentException("Architecture error: successResponse does not accept {$type}. Use Model, Paginator, array, bool or null.");
        }

        $response = [
            'success' => true,
            'message' => $message,
        ];

        // 4. Format final result
        if ($data instanceof ResourceCollection) {
            $responseData = $data->toArray(request());
            $response = array_merge($response, $responseData);
        } elseif ($data instanceof JsonResource) {
            $response['data'] = $data->toArray(request());
        } elseif ($data !== null) {
            $response['data'] = $data;
        }

        return $this->jsonResponseWithPreserveDecimal($response, $status);
    }

    /**
     * Maps an exception to a standard response format
     * Centralized here so bootstrap/app.php and handleError use the same.
     */
    public static function parseExceptionPayload(\Throwable $e, int $defaultCode = 500): array
    {
        $status = $defaultCode;
        $message = 'An unexpected error has occurred';
        $errors = [];

        if ($e instanceof ValidationException) {
            $status = 422;
            $message = 'Validation error';
            $errors = $e->errors();
        } elseif ($e instanceof ModelNotFoundException) {
            $status = 404;
            $message = 'Resource not found';
        } elseif ($e instanceof NotFoundHttpException) {
            $status = 404;
            $message = 'Route not found';
        } elseif ($e instanceof AuthenticationException) {
            $status = 401;
            $message = 'Unauthenticated';
        } elseif ($e instanceof AuthorizationException || $e instanceof \Illuminate\Auth\Access\AuthorizationException || $e instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException) {
            $status = 403;
            $message = 'You do not have permission to perform this action';
        } elseif ($e instanceof MethodNotAllowedHttpException) {
            $status = 405;
            $message = 'Method not allowed';
        } elseif ($e instanceof ThrottleRequestsException) {
            $status = 429;
            $message = 'Too many requests';
        } elseif ($e instanceof QueryException) {
            $status = 500;
            $message = 'Database error';
            if (config('app.debug')) {
                $errors['sql'] = $e->getMessage();
            }
        } elseif (config('app.debug')) {
            $status = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface ? $e->getStatusCode() : $status;
            $message = $e->getMessage() ?: $message;
            $errors = [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
        }

        return [
            'status' => $status,
            'message' => $message,
            'errors' => $errors,
        ];
    }

    /**
     * Transforms a resource collection
     */
    protected function transformCollection(LengthAwarePaginator $collection, ?string $resourceClass = null): ResourceCollection
    {
        $model = $collection->first();
        $collectionClass = $model ? $this->getCollectionClass($model) : ApiCollection::class;

        if ($collectionClass === ApiCollection::class || $collectionClass === ResourceCollection::class) {
            // If using the base class, ensure it uses the correct Resource for items
            $resourceCollection = $resourceClass ? $resourceClass::collection($collection) : new ApiCollection($collection);

            // If it's Laravel's anonymous one, wrap it in our ApiCollection to have meta/links format
            return ($resourceCollection instanceof ApiCollection) ? $resourceCollection : new ApiCollection($collection);
        }

        return new $collectionClass($collection);
    }

    /**
     * Transforms an individual resource
     */
    protected function transformResource($resource): JsonResource
    {
        if (!$resource instanceof Model) {
            return new JsonResource($resource);
        }

        $resourceClass = $this->getResourceClass($resource);
        return new $resourceClass($resource);
    }

    /**
     * Gets the Resource class for a model
     */
    protected function getResourceClass(Model $model): string
    {
        $modelName = class_basename($model);
        $resourceClass = "App\\Http\\Resources\\{$modelName}Resource";

        if (!class_exists($resourceClass)) {
            return JsonResource::class;
        }

        return $resourceClass;
    }

    /**
     * Gets the Collection class for a model
     */
    protected function getCollectionClass(Model $model): string
    {
        $modelName = class_basename($model);
        $collectionClass = "App\\Http\\Resources\\{$modelName}Collection";

        if (!class_exists($collectionClass)) {
            return ApiCollection::class;
        }

        return $collectionClass;
    }

    /**
     * Creates a JSON response with JSON_PRESERVE_ZERO_FRACTION
     * so floats are serialized correctly (300.0 instead of 300)
     */
    protected function jsonResponseWithPreserveDecimal(array $data, int $status = 200): JsonResponse
    {
        return response()->json(
            $data,
            $status,
            [],
            JSON_PRESERVE_ZERO_FRACTION
        );
    }
}
