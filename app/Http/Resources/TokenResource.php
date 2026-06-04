<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TokenResource extends JsonResource
{
    protected ?string $secret;

    public function __construct($resource, ?string $secret = null)
    {
        parent::__construct($resource);

        $this->secret = $secret;
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name ?? $this->client?->name,
            'secret' => $this->whenNotNull($this->secret),
            'revoked' => (bool) $this->revoked,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'expires_at' => $this->expires_at,
        ];
    }
}
