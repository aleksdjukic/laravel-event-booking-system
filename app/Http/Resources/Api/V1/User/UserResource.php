<?php

namespace App\Http\Resources\Api\V1\User;

use App\Domain\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->{User::COL_ID},
            'name' => $this->{User::COL_NAME},
            'email' => $this->{User::COL_EMAIL},
            'phone' => $this->{User::COL_PHONE},
            'role' => $this->roleValue(),
            'created_at' => $this->{User::COL_CREATED_AT},
            'updated_at' => $this->{User::COL_UPDATED_AT},
        ];
    }
}
