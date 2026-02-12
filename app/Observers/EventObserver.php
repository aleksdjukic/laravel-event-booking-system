<?php

namespace App\Observers;

use App\Models\Event;
use Illuminate\Support\Facades\Cache;

class EventObserver
{
    public function created(Event $event): void
    {
        $this->bumpEventIndexVersion();
    }

    public function updated(Event $event): void
    {
        $this->bumpEventIndexVersion();
    }

    public function deleted(Event $event): void
    {
        $this->bumpEventIndexVersion();
    }

    public function restored(Event $event): void
    {
        $this->bumpEventIndexVersion();
    }

    private function bumpEventIndexVersion(): void
    {
        if (! Cache::has('events:index:version')) {
            Cache::forever('events:index:version', 1);
        }

        Cache::increment('events:index:version');
    }
}
