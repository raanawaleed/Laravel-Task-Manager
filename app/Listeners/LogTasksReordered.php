<?php

namespace App\Listeners;

use App\Events\TasksReordered;
use Illuminate\Support\Facades\Log;

class LogTasksReordered
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TasksReordered $event): void
    {
        Log::info('[Event] Tasks Reordered', [
            'new_order' => $event->taskIds,
            'timestamp' => now()->toISOString(),
        ]);
    }
}
