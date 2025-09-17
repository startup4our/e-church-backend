<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use App\Models\Area;
use App\Models\PermissionTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionTemplateControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_permission_templates()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        PermissionTemplate::factory()->count(2)->create();

        $response = $this->getJson('/api/v1/permission-templates');

        $response->assertStatus(200)
                 ->assertJsonCount(2, 'data');
    }

    public function test_show_returns_permission_template()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $template = PermissionTemplate::factory()->create();

        $response = $this->getJson("/api/v1/permission-templates/{$template->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $template->id]);
    }

    public function test_store_creates_permission_template()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $area = Area::factory()->create();

        $data = [
            'name'        => 'Leader default',
            'description' => 'Default permissions for leaders',
            'area_id'     => $area->id,
            'create_scale' => true,
            'read_scale'   => true,
        ];

        $response = $this->postJson('/api/v1/permission-templates', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['name' => 'Leader default']);

        $this->assertDatabaseHas('permission_templates', [
            'name'       => 'Leader default',
            'created_by' => $user->id,
        ]);
    }

    public function test_update_modifies_permission_template()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $template = PermissionTemplate::factory()->create();

        $response = $this->putJson("/api/v1/permission-templates/{$template->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Updated Name']);

        $this->assertDatabaseHas('permission_templates', [
            'id'   => $template->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_destroy_deletes_permission_template()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $template = PermissionTemplate::factory()->create();

        $response = $this->deleteJson("/api/v1/permission-templates/{$template->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('permission_templates', ['id' => $template->id]);
    }
}
