<?php

namespace App\Models\Traits;

use DateTime;
use App\Casts\CompressedCollection;
use Illuminate\Database\Eloquent\Relations\HasOneOrManyThrough;
use LogicException;

trait Revisionable
{
    protected function initializeRevisionable(): void
    {
        $this->attributes['history'] = null;

        $this->mergeCasts([
            'history' => CompressedCollection::class,
        ]);
    }

    public static function bootRevisionable()
    {
        static::updated(function ($model) {
            $data = array_intersect_key($model->getOriginal(), $model->getChanges());

            $model->saveRevision($data);
        });

        // Laravel thinks we're calling the method trashed() if we don't use registerModelEvent()
        static::registerModelEvent('trashed', fn($model) => $model->saveRevision(['deleted_at' => null]));
    }

    /* Helpers */

    public function saveRevision($data)
    {
        $this->history->push([
            'timestamp' => now()->toISOString(),
            'data' => $data,
        ]);

        $this->saveQuietly();

        return $this;
    }

    public function rollBack(DateTime $timestamp, $with = [])
    {
        if ($timestamp < $this->created_at) {
            throw new LogicException('Cannot roll back before model was created.');
        }

        $this->history
            ->where('timestamp', '>', $timestamp->toISOString())
            ->sortByDesc('timestamp')
            ->each(fn($revision) => $this->forceFill($revision['data']));

        foreach ($with as $relation) {
            $this->load([
                $relation => function ($query) use ($timestamp) {
                    if ($query instanceof HasOneOrManyThrough) {
                        $query->withTrashedParents();
                    }

                    return $query
                        ->withTrashed()
                        ->afterQuery(fn($models) => $models
                            ->where('created_at', '<', $timestamp->toDateTimeString())
                            ->each->rollBack($timestamp)
                            ->whereNull('deleted_at'));
                },
            ]);
        }
    }
}
