<?php

namespace App\Http\Resources\Api\V1;

use App\Enums\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $role = $this->role instanceof Role ? $this->role->value : (string) $this->role;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $role,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
