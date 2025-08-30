<?php

namespace Tests\Unit;

use App\Models\Unavailability;
use App\Models\User;
use App\Repositories\UnavailabilityRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnavailabilityRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected UnavailabilityRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new UnavailabilityRepository();
    }

    public function test_create_and_find_by_id()
    {
        $user = User::factory()->create();
        $data = ['user_id' => $user->id, 'weekday' => 1, 'shift' => 'manha'];

        $created = $this->repository->create($data);

        $this->assertDatabaseHas('unavailability', $data);

        $found = $this->repository->findById($created->id);

        $this->assertNotNull($found);
        $this->assertEquals($user->id, $found->user_id);
    }

    public function test_exists_returns_true_for_duplicate()
    {
        $unavailability = Unavailability::factory()->create();

        $data = [
            'user_id' => $unavailability->user_id,
            'weekday' => $unavailability->weekday,
            'shift'   => $unavailability->shift,
        ];

        $this->assertTrue($this->repository->exists($data));
    }

    public function test_update_changes_fields()
    {
        $unavailability = Unavailability::factory()->create(['weekday' => 1, 'shift' => 'manha']);

        $updated = $this->repository->update($unavailability, ['weekday' => 2, 'shift' => 'tarde']);

        $this->assertEquals(2, $updated->weekday);
        $this->assertEquals('tarde', $updated->shift);
        $this->assertDatabaseHas('unavailability', [
            'id' => $unavailability->id,
            'weekday' => 2,
            'shift' => 'tarde',
        ]);
    }

    public function test_delete_removes_record()
    {
        $unavailability = Unavailability::factory()->create();

        $this->repository->delete($unavailability);

        $this->assertDatabaseMissing('unavailability', ['id' => $unavailability->id]);
    }
}
