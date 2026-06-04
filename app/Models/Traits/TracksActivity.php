<?php

namespace App\Models\Traits;

trait TracksActivity
{
    protected function initializeTracksActivity(): void
    {
        $this->mergeCasts([
            'activity_at' => 'datetime',
        ]);
    }

    public function trackActivity()
    {
        self::withoutTimestamps(function () {
            $this->forceFill([
                'activity_at' => now(),
            ])->saveQuietly();
        });

        $this->handleActivity();
    }

    protected function handleActivity()
    {
        // Override this to call trackActivity on parent(s)
    }
}
