<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'commission_rate' => $this->commission_rate,
            'notes' => $this->notes,
            'role' => $this->role,
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'slug' => $role->slug,
                    ];
                });
            }),
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'total_sales' => $this->when(isset($this->total_sales), $this->total_sales),
            'total_revenue' => $this->when(isset($this->total_revenue), $this->total_revenue),
            'total_commission' => $this->when(isset($this->total_commission), $this->total_commission),
        ];
    }
}

