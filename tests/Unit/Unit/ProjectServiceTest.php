<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProjectService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ProjectService();
    }

    public function test_get_all_projects_returns_collection(): void
    {
        Project::factory()->count(3)->create();

        $projects = $this->service->getAllProjects();

        $this->assertCount(3, $projects);
    }

    public function test_create_project_saves_to_database(): void
    {
        $project = $this->service->createProject([
            'name'        => 'Test Project',
            'description' => 'A description',
        ]);

        $this->assertDatabaseHas('projects', ['name' => 'Test Project']);
        $this->assertInstanceOf(Project::class, $project);
    }

    public function test_update_project_persists_changes(): void
    {
        $project = Project::factory()->create(['name' => 'Old']);
        $updated = $this->service->updateProject($project, ['name' => 'New', 'description' => null]);

        $this->assertEquals('New', $updated->name);
    }

    public function test_delete_project_removes_from_database(): void
    {
        $project = Project::factory()->create();
        $id      = $project->id;

        $result = $this->service->deleteProject($project);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('projects', ['id' => $id]);
    }
}
