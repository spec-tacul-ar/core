<?php

namespace App\Models\Traits;

use App\Events\ItemChanged;
use App\Events\ProjectItemDeleted;
use App\Events\ProjectItemSaved;

trait BroadcastsActivity
{
    public static function bootBroadcastsActivity()
    {
        static::saved(function ($model) {
            broadcast(new ProjectItemSaved($model));
        });

        static::deleted(function ($model) {
            broadcast(new ProjectItemDeleted($model));
        });
    }
}
