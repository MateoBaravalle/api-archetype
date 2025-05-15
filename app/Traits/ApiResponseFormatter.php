<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
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

/**
 * Trait ApiResponseFormatter
 *
 * Proporciona métodos para formatear y manejar respuestas de la API,
 * incluyendo el manejo de excepciones y la transformación de recursos.
 */
trait ApiResponseFormatter
{
    /**
     * Formatea una respuesta exitosa
     */
    protected function successResponse($data, string $message = 'Operación exitosa', int $status = 200): JsonResponse
    {
        if ($data instanceof ResourceCollection || $data instanceof JsonResource) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $data->toArray(request()),
            ], $status);
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Formatea una respuesta de error
     */
    protected function errorResponse(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }

    /**
     * Maneja errores y las convierte en respuestas JSON apropiadas
     */
    protected function handleError(\Exception $e, int $defaultCode = 400): JsonResponse
    {
        Log::error('Error en la aplicación: ' . $e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        $errorData = match (true) {
            $e instanceof ModelNotFoundException => [
                'message' => 'Recurso no encontrado',
                'code' => 404,
            ],
            $e instanceof AuthenticationException => [
                'message' => 'Autenticación fallida',
                'code' => 401,
            ],
            $e instanceof AuthorizationException => [
                'message' => 'Autorización fallida',
                'code' => 403,
            ],
            $e instanceof QueryException => [
                'message' => 'Error en base de datos',
                'code' => 500,
            ],
            $e instanceof \InvalidArgumentException => [
                'message' => 'Parámetro inválido',
                'code' => 400,
            ],
            $e instanceof ValidationException => [
                'message' => 'Error de validación',
                'code' => 422,
            ],
            $e instanceof NotFoundHttpException => [
                'message' => 'Ruta no encontrada',
                'code' => 404,
            ],
            $e instanceof MethodNotAllowedHttpException => [
                'message' => 'Método no permitido',
                'code' => 405,
            ],
            $e instanceof ThrottleRequestsException => [
                'message' => 'Demasiadas solicitudes',
                'code' => 429,
            ],
            $e instanceof TokenMismatchException => [
                'message' => 'Token CSRF no válido',
                'code' => 419,
            ],
            default => [
                'message' => 'Operación fallida',
                'code' => $defaultCode,
            ]
        };

        $errors = [];

        if ($e->getMessage()) {
            $errors['exception'] = $e->getMessage();
        }

        return $this->errorResponse($errorData['message'], $errorData['code'], $errors);
    }

    /**
     * Transforma una colección de recursos
     */
    protected function transformCollection(LengthAwarePaginator $collection): ResourceCollection
    {
        $model = $collection->first();
        if (!$model) {
            return new ResourceCollection($collection);
        }

        $collectionClass = $this->getCollectionClass($model);
        return new $collectionClass($collection);
    }

    /**
     * Transforma un recurso individual
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
     * Obtiene la clase de Resource para un modelo
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
     * Obtiene la clase de Collection para un modelo
     */
    protected function getCollectionClass(Model $model): string
    {
        $modelName = class_basename($model);
        $collectionClass = "App\\Http\\Resources\\{$modelName}Collection";

        if (!class_exists($collectionClass)) {
            return ResourceCollection::class;
        }

        return $collectionClass;
    }
}
