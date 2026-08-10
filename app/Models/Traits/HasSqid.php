<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Sqids\SqidsInterface;

trait HasSqid
{
    protected SqidsInterface $sqids;

    protected function initializeHasSqid(): void
    {
        $this->sqids = app(SqidsInterface::class);
    }

    public function getRouteKeyName(): string
    {
        return 'sqid';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        if ($field === null) {
            $ids = $this->sqids->decode($value);

            if (count($ids) !== 1 || $this->sqids->encode($ids) !== $value) {
                throw (new ModelNotFoundException())->setModel(static::class, $value);
            }

            return $this->findOrFail($ids[0]);
        }

        return parent::resolveRouteBinding($value, $field);
    }

    public function resolveSoftDeletableRouteBinding($value, $field = null)
    {
        if ($field === null) {
            $ids = $this->sqids->decode($value);

            if (count($ids) !== 1 || $this->sqids->encode($ids) !== $value) {
                return null;
            }

            return $this->newQuery()->withTrashed()->find($ids[0]);
        }

        return parent::resolveSoftDeletableRouteBinding($value, $field);
    }

    protected function sqid(): Attribute
    {
        $id = $this->getKey();

        $sqid = $id ? $this->sqids->encode([$id]) : null;

        return Attribute::make(
            get: fn() => $sqid,
        );
    }

    public function obfuscateIdentifiers(array $attributes): array
    {
        foreach ($attributes as $key => $value) {
            if ($key === $this->getKeyName()) {
                $attributes[$key] = $this->sqid;

                continue;
            }

            if (!Str::endsWith($key, '_id')) {
                continue;
            }

            $sqidKey = Str::replaceEnd('_id', '_sqid', $key);

            if ($this->hasCast($sqidKey)) {
                $attributes[$key] = $this->{$sqidKey};
            }
        }

        return $attributes;
    }
}
