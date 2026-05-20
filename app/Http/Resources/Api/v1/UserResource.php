<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => "https://ui-avatars.com/api/?name=" . urlencode($this->name) . "&background=random",
            'created_at' => $this->created_at->format('Y-m-d'),
        ];
    }
}
