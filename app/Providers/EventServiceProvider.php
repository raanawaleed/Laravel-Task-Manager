<?php

namespace App\Providers;

use App\Events\TaskCreated;
use App\Events\TaskDeleted;
use App\Events\TaskUpdated;
use App\Events\TasksReordered;
use App\Listeners\LogTaskCreated;
use App\Listeners\LogTaskDeleted;
use App\Listeners\LogTaskUpdated;
use App\Listeners\LogTasksReordered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TaskCreated::class   => [LogTaskCreated::class],
        TaskUpdated::class   => [LogTaskUpdated::class],
        TaskDeleted::class   => [LogTaskDeleted::class],
        TasksReordered::class => [LogTasksReordered::class],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
