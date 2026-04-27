<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Sqids\SqidsInterface;

class AsSqid implements Castable
{
    public static function castUsing(array $arguments): CastsAttributes
    {
        $field = $arguments[0] ?? null;

        return new class ($field) implements CastsAttributes {
            public function __construct(private ?string $field)
            {
                //
            }

            public function get(Model $model, string $key, mixed $value, array $attributes): ?string
            {
                if (!$this->field) {
                    $this->field = Str::replaceEnd('_sqid', '_id', $key);
                }

                $id = $attributes[$this->field] ?? null;

                return $id ? app(SqidsInterface::class)->encode([$id]) : null;
            }

            public function set(Model $model, string $key, mixed $value, array $attributes): array
            {
                if (!$this->field) {
                    $this->field = Str::replaceEnd('_sqid', '_id', $key);
                }

                $ids = app(SqidsInterface::class)->decode((string) $value);

                $id = count($ids) === 1 ? $ids[0] : null;

                return [
                    $this->field => $id,
                ];
            }
        };
    }
}
