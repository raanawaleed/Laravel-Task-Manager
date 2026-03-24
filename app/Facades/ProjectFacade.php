<?php

namespace App\Facades;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Collection getAllProjects()
 * @method static Project    createProject(array $data)
 * @method static Project    updateProject(Project $project, array $data)
 * @method static bool       deleteProject(Project $project)
 *
 * @see \App\Services\ProjectService
 */
class ProjectFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'project.service';
    }
}
