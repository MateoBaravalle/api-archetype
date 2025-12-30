<?php

declare(strict_types=1);

namespace Tests\Unit\Requests;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Prueba la sanitización de datos de entrada
     */
    public function test_input_sanitization(): void
    {
        $request = new class extends ApiRequest
        {
            public function rules(): array
            {
                return [
                    'name' => 'required|string',
                    'email' => 'required|email',
                    'description' => 'nullable|string',
                ];
            }
        };

        $input = [
            'name' => '  Juan   Pérez  ',
            'email' => 'juan@example.com<script>alert("xss")</script>',
            'description' => "  Línea 1\n  Línea 2  \n  Línea 3  ",
        ];

        $request->merge($input);
        $request->prepareForValidation();

        $this->assertEquals('Juan Pérez', $request->input('name'));
        $this->assertEquals('juan@example.com<script>alert("xss")</script>', $request->input('email'));
        $this->assertEquals('Línea 1 Línea 2 Línea 3', $request->input('description'));
    }

    /**
     * Prueba que los caracteres de control son eliminados
     */
    public function test_control_characters_are_removed(): void
    {
        $request = new class extends ApiRequest
        {
            public function rules(): array
            {
                return [
                    'text' => 'required|string',
                ];
            }
        };

        $input = [
            'text' => "Texto con \x00 caracteres \x1F de control \x7F",
        ];

        $request->merge($input);
        $request->prepareForValidation();

        $this->assertEquals('Texto con caracteres de control', trim($request->input('text')));
    }

    /**
     * Prueba que los espacios múltiples son normalizados
     */
    public function test_multiple_spaces_are_normalized(): void
    {
        $request = new class extends ApiRequest
        {
            public function rules(): array
            {
                return [
                    'text' => 'required|string',
                ];
            }
        };

        $input = [
            'text' => 'Texto    con    muchos    espacios',
        ];

        $request->merge($input);
        $request->prepareForValidation();

        $this->assertEquals('Texto con muchos espacios', $request->input('text'));
    }
}
