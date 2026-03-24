<?php

namespace App\Listeners;

use App\Events\TasksReordered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

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
        //
    }
}
