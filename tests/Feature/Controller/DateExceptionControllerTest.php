<?php

namespace Tests\Feature;

use App\Models\DateException;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DateExceptionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_exception()
    {
        $user = User::factory()->create();

        // autentica o usuário e adiciona o header Authorization: Bearer
        $this->authenticate($user);

        $response = $this->postJson('/api/v1/date-exception', [
            'exception_date' => "2025-01-01",
            'shift' => 'morning',
            'justification' => 'Motivo teste',
            'user_id' => $user->id,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('date_exceptions', [
            'user_id' => $user->id,
            'shift' => 'morning',
            'justification' => 'Motivo teste',
        ]);
    }
}
