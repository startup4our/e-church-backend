<?php

namespace Tests\Feature;

use App\Models\Unavailability;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnavailabilityControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_creates_unavailability()
    {
        $user = User::factory()->create();

        $response = $this->postJson('api/v1/unavailability', [
            'user_id' => $user->id,
            'weekday' => 1,
            'shift' => 'manha',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('unavailability', [
            'user_id' => $user->id,
            'weekday' => 1,
            'shift' => 'manha',
        ]);
    }
}
