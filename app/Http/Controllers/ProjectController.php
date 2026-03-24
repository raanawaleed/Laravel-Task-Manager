<?php

namespace App\Http\Controllers;

use App\Facades\ProjectFacade;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Project::class);

        try {
            $projects = ProjectFacade::getAllProjects();
            return view('projects.index', compact('projects'));
        } catch (\Throwable $e) {
            Log::error('ProjectController::index failed', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Unable to load projects.']);
        }
    }

    public function create()
    {
        $this->authorize('create', Project::class);
        return view('projects.create');
    }

    public function store(StoreProjectRequest $request)
    {
        $this->authorize('create', Project::class);

        try {
            $project = ProjectFacade::createProject($request->validated());

            return redirect()->route('projects.index')
                ->with('success', "Project '{$project->name}' created successfully.");
        } catch (\Throwable $e) {
            Log::error('ProjectController::store failed', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Failed to create project.'])->withInput();
        }
    }

    public function edit(Project $project)
    {
        $this->authorize('update', $project);
        return view('projects.edit', compact('project'));
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $this->authorize('update', $project);

        try {
            $updated = ProjectFacade::updateProject($project, $request->validated());

            return redirect()->route('projects.index')
                ->with('success', "Project '{$updated->name}' updated successfully.");
        } catch (\Throwable $e) {
            Log::error('ProjectController::update failed', [
                'project_id' => $project->id,
                'error'      => $e->getMessage(),
            ]);
            return back()->withErrors(['error' => 'Failed to update project.'])->withInput();
        }
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        try {
            ProjectFacade::deleteProject($project);

            return redirect()->route('projects.index')
                ->with('success', 'Project deleted successfully.');
        } catch (\Throwable $e) {
            Log::error('ProjectController::destroy failed', [
                'project_id' => $project->id,
                'error'      => $e->getMessage(),
            ]);
            return back()->withErrors(['error' => 'Failed to delete project.']);
        }
    }
}
