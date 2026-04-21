<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->when($request->user()->is($this->resource), $this->email),
            'role' => $this->whenLoaded('contributor', fn () => $this->contributor->role),
            'is_email_verified' => $this->when($request->user()->is($this->resource), $this->hasVerifiedEmail()),
        ];
    }
}
