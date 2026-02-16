<?php

namespace App\Modules\Event\Application\Actions;

use App\Domain\Event\Support\EventCache;
use Illuminate\Support\Facades\Cache;

class BumpEventIndexVersionAction
{
    public function execute(): void
    {
        if (! Cache::has(EventCache::INDEX_VERSION_KEY)) {
            Cache::forever(EventCache::INDEX_VERSION_KEY, 1);
        }

        Cache::increment(EventCache::INDEX_VERSION_KEY);
    }
}
