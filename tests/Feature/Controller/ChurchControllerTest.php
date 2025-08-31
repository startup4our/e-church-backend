<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Church;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChurchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_churches()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        Church::factory()->count(2)->create();

        $response = $this->getJson('/api/v1/churches');

        $response->assertStatus(200)
                 ->assertJsonCount(3); //3 because userfactory creates one too
    }

    public function test_show_returns_church()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $church = Church::factory()->create();

        $response = $this->getJson("/api/v1/churches/{$church->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $church->id]);
    }

    public function test_store_creates_church()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $data = [
            'name'       => 'Igreja Central',
            'cep'        => '37500-000',
            'street'     => 'Av. Brasil',
            'number'     => '120',
            'complement' => 'Sala 2',
            'quarter'    => 'Centro',
            'city'       => 'Itajubá',
            'state'      => 'MG',
        ];

        $response = $this->postJson('/api/v1/churches', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['name' => 'Igreja Central']);

        $this->assertDatabaseHas('church', ['name' => 'Igreja Central', 'city' => 'Itajubá']);
    }

    public function test_update_modifies_church()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $church = Church::factory()->create(['name' => 'Antiga']);

        $response = $this->putJson("/api/v1/churches/{$church->id}", [
            'name' => 'Atualizada',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Atualizada']);

        $this->assertDatabaseHas('church', ['id' => $church->id, 'name' => 'Atualizada']);
    }

    public function test_destroy_deletes_church()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $church = Church::factory()->create();

        $response = $this->deleteJson("/api/v1/churches/{$church->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('church', ['id' => $church->id]);
    }
}
