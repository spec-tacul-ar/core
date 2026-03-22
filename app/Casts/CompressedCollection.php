<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Collection;
use JsonException;
use RuntimeException;

class CompressedCollection implements Castable
{
    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class implements CastsAttributes {
            public function get($model, string $key, mixed $value, array $attributes): Collection
            {
                if (!$value) {
                    return collect();
                }

                $json = gzdecode($value);

                if ($json === false) {
                    throw new RuntimeException('Failed to decompress collection.');
                }

                try {
                    $data = json_decode($json, associative: true, flags: JSON_THROW_ON_ERROR);
                } catch (JsonException $exception) {
                    throw new RuntimeException('Failed to decode collection.', previous: $exception);
                }

                return collect($data);
            }

            public function set($model, string $key, $value, array $attributes): ?string
            {
                if (!is_array($value) && !($value instanceof Collection)) {
                    return null;
                }

                $collection = collect($value);

                if ($collection->isEmpty()) {
                    return null;
                }

                try {
                    $json = json_encode($collection->all(), JSON_THROW_ON_ERROR);
                } catch (JsonException $exception) {
                    throw new RuntimeException('Failed to encode collection.', previous: $exception);
                }

                $compressed = gzencode($json);

                if ($compressed === false) {
                    throw new RuntimeException('Failed to compress collection.');
                }

                return $compressed;
            }
        };
    }
}
