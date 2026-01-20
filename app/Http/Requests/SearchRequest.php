<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Support\Str;

/**
 * Global request for search, pagination, and filter validation.
 * 
 * Use it in the index() methods of your controllers to ensure
 * that sorting, pagination, and range parameters are safe.
 */
class SearchRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'sort_by' => 'sometimes|string|max:50',
            'sort_order' => 'sometimes|in:asc,desc,ASC,DESC',
            'filters' => 'sometimes|array',
        ];

        // Dynamically validate any detected range filter
        foreach ($this->all() as $key => $value) {
            // Ignore empty values
            if ($value === null || $value === '') {
                continue;
            }

            // 1. Validate Date Ranges (_start / _end)
            if (Str::endsWith($key, '_start')) {
                $rules[$key] = 'date';
            }
            if (Str::endsWith($key, '_end')) {
                $rules[$key] = 'date';

                // Consistency validation: End Date >= Start Date
                $prefix = substr($key, 0, -4);
                if ($this->has($prefix . '_start')) {
                    $rules[$key] .= '|after_or_equal:' . $prefix . '_start';
                }
            }

            // 2. Validate Numeric Ranges (_min / _max)
            if (Str::endsWith($key, '_min')) {
                $rules[$key] = 'numeric';
            }
            if (Str::endsWith($key, '_max')) {
                $rules[$key] = 'numeric';

                // Consistency validation: Maximum >= Minimum
                $prefix = substr($key, 0, -4);
                if ($this->has($prefix . '_min')) {
                    $rules[$key] .= '|gte:' . $prefix . '_min';
                }
            }
        }

        // Allow any other query string parameter to pass validation
        // This is necessary for $request->validated() to include 'status', 'priority', etc.
        foreach ($this->query() as $key => $value) {
            if (! isset($rules[$key])) {
                $rules[$key] = 'sometimes';
            }
        }

        return $rules;
    }

    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [
            'page.integer' => 'The page number must be an integer.',
            'page.min' => 'The page number must be at least 1.',
            'per_page.max' => 'The number of records per page cannot exceed 100.',
            'sort_order.in' => 'The order must be asc or desc.',
            '*.date' => 'The field must be a valid date.',
            '*.after_or_equal' => 'The end date must be after or equal to the start date.',
            '*.numeric' => 'The field must be a number.',
            '*.gte' => 'The maximum value must be greater than or equal to the minimum value.',
        ];
    }
}
