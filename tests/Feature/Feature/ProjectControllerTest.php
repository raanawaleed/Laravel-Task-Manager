<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_guests_cannot_access_projects(): void
    {
        $this->get(route('projects.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_project_list(): void
    {
        Project::factory()->count(2)->create();

        $this->actingAs($this->user)
            ->get(route('projects.index'))
            ->assertOk()
            ->assertViewIs('projects.index')
            ->assertViewHas('projects');
    }

    public function test_can_view_create_project_form(): void
    {
        $this->actingAs($this->user)
            ->get(route('projects.create'))
            ->assertOk()
            ->assertViewIs('projects.create');
    }

    public function test_can_create_a_project(): void
    {
        $this->actingAs($this->user)
            ->post(route('projects.store'), [
                'name'        => 'New Project',
                'description' => 'A description',
            ])
            ->assertRedirect(route('projects.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('projects', ['name' => 'New Project']);
    }

    public function test_create_project_validates_name_required(): void
    {
        $this->actingAs($this->user)
            ->post(route('projects.store'), ['name' => '', 'description' => null])
            ->assertSessionHasErrors('name');
    }

    public function test_can_view_edit_project_form(): void
    {
        $project = Project::factory()->create();

        $this->actingAs($this->user)
            ->get(route('projects.edit', $project))
            ->assertOk()
            ->assertViewIs('projects.edit')
            ->assertViewHas('project', $project);
    }

    public function test_can_update_a_project(): void
    {
        $project = Project::factory()->create(['name' => 'Old']);

        $this->actingAs($this->user)
            ->put(route('projects.update', $project), [
                'name'        => 'Updated',
                'description' => null,
            ])
            ->assertRedirect(route('projects.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('projects', ['id' => $project->id, 'name' => 'Updated']);
    }

    public function test_can_delete_a_project(): void
    {
        $project = Project::factory()->create();

        $this->actingAs($this->user)
            ->delete(route('projects.destroy', $project))
            ->assertRedirect(route('projects.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }
}
