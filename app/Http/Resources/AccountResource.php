<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->sqid,
            'name' => $this->name,
            'email' => $this->when($request->user()->is($this->resource), $this->email),
            'role' => $this->whenLoaded('collaboration', fn() => $this->collaboration->role),
        ];
    }
}
