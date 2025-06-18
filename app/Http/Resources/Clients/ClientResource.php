<?php

namespace App\Http\Resources\Clients;

use Illuminate\Http\Request;
use App\Http\Resources\Users\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "user" => new UserResource($this->user, true),
        ];
    }
}
