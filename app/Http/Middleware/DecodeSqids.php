<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use RuntimeException;
use Sqids\SqidsInterface;
use Symfony\Component\HttpFoundation\Response;

class DecodeSqids
{
    public function __construct(protected SqidsInterface $sqids)
    {
        //
    }

    public function handle(Request $request, Closure $next, string ...$fields): Response
    {
        $data = $request->all();

        foreach ($fields as $field) {
            $parts = array_map(fn($part) => trim($part, '.'), explode('*', $field));

            if (count($parts) > 2) {
                throw new RuntimeException('Too many wildcards in field name.');
            }

            if (!Arr::has($data, $parts[0])) {
                continue;
            }

            if (count($parts) === 1) {
                $sqid = data_get($data, $field);

                $id = $this->decode($sqid);

                data_set($data, $field, $id);
            } elseif (!$parts[1]) {
                $sqids = data_get($data, $parts[0]);

                $ids = array_map(fn($sqid) => $this->decode($sqid), $sqids);

                data_set($data, $parts[0], $ids);
            } else {
                $items = array_map(function ($item) use ($parts) {
                    if (!Arr::has($item, $parts[1])) {
                        return $item;
                    }

                    $sqid = data_get($item, $parts[1]);

                    $id = $this->decode($sqid);

                    return data_set($item, $parts[1], $id);
                }, data_get($data, $parts[0]));

                data_set($data, $parts[0], $items);
            }
        }

        $request->replace($data);

        return $next($request);
    }

    protected function decode(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        $ids = $this->sqids->decode((string) $value);

        return count($ids) === 1 ? $ids[0] : null;
    }
}
