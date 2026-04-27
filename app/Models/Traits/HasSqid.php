<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
            $ids = app(SqidsInterface::class)->decode($value);

            if (count($ids) !== 1) {
                throw (new ModelNotFoundException())->setModel(static::class, $value);
            }

            return $this->findOrFail($ids[0]);
        }

        return parent::resolveRouteBinding($value, $field);
    }

    protected function sqid(): Attribute
    {
        $id = $this->getKey();

        $sqid = $id ? app(SqidsInterface::class)->encode([$id]) : null;

        return Attribute::make(
            get: fn() => $sqid,
        );
    }
}
