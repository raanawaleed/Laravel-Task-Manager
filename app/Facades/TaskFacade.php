<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class TaskFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'task.service';
    }
}
