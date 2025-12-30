<?php

declare(strict_types=1);

namespace App\Http\Resources;

class AuthResource extends ApiResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'token' => $this->when($this->token, $this->token),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
