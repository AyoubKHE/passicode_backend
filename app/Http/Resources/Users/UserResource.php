<?php

namespace App\Http\Resources\Users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    private $show_only_user;

    public function __construct($resource, $show_only_user = false)
    {
        parent::__construct($resource);
        $this->show_only_user = $show_only_user;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = [
            "id" => $this->id,
            "first_name" => $this->first_name,
            "last_name" => $this->last_name,
            "email" => $this->email,
            "image_url" => $this->image_url,
            "role" => $this->role,
            "is_active" => $this->is_active,
            "last_login" => $this->last_login,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at
        ];

        if (!$this->show_only_user) {
            if ($this->role === "Super Admin" || $this->role === "Admin") {
                $user["admin"]["id"] = $this->admin->id;
            } else if ($this->role === "Client") {
                $user["client"]["id"] = $this->client->id;
            }
        }

        return $user;
    }
}
