<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Sqids\SqidsInterface;

trait HasSqid
{
    public function getRouteKeyName(): string
    {
        return 'sqid';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        if ($field === null) {
            $key = $this->sqidToId($value);

            return $this->findOrFail($key);
        }

        return parent::resolveRouteBinding($value, $field);
    }

    protected function sqid(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->idToSqid(),
        );
    }

    public function idToSqid(?string $field = null)
    {
        $id = $field === null ? $this->getKey() : $this->{$field};

        return $id ? app(SqidsInterface::class)->encode([$id]) : null;
    }

    public static function sqidToId(string $sqid)
    {
        $ids = app(SqidsInterface::class)->decode($sqid);

        return count($ids) === 1 ? array_first($ids) : null;
    }

    #[Scope]
    protected function whereSqid(Builder $query, string $sqid): void
    {
        $query->whereKey($this->sqidToId($sqid));
    }
}
