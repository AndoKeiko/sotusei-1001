<?php

namespace App\Listeners;

use App\Events\SomeEvent;
use App\Jobs\SomeJob;

class SomeListener
{
    /**
     * Handle the event.
     */
    public function handle(SomeEvent $event): void
    {
        // ジョブをディスパッチ（キューに追加）
        SomeJob::dispatch($event->eventData);
    }
}
