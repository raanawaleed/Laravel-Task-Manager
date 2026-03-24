<?php

namespace App\Services;

use App\Exceptions\ProjectException;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectService
{
    /**
     * Get all projects with task counts.
     */
    public function getAllProjects(): Collection
    {
        try {
            return Project::withCount('tasks')->orderBy('name')->get();
        } catch (\Throwable $e) {
            Log::error('ProjectService::getAllProjects failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Create a new project.
     */
    public function createProject(array $data): Project
    {
        try {
            DB::beginTransaction();

            $project = Project::create($data);

            DB::commit();

            Log::info('Project created', ['project_id' => $project->id]);

            return $project;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('ProjectService::createProject failed', [
                'data'  => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw ProjectException::createFailed($e->getMessage());
        }
    }

    /**
     * Update an existing project.
     */
    public function updateProject(Project $project, array $data): Project
    {
        try {
            DB::beginTransaction();

            $project->update($data);

            DB::commit();

            Log::info('Project updated', ['project_id' => $project->id]);

            return $project->fresh();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('ProjectService::updateProject failed', [
                'project_id' => $project->id,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);
            throw ProjectException::updateFailed($e->getMessage());
        }
    }

    /**
     * Delete a project.
     */
    public function deleteProject(Project $project): bool
    {
        try {
            DB::beginTransaction();

            $project->delete();

            DB::commit();

            Log::info('Project deleted', ['project_id' => $project->id]);

            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('ProjectService::deleteProject failed', [
                'project_id' => $project->id,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);
            throw ProjectException::deleteFailed($e->getMessage());
        }
    }
}
