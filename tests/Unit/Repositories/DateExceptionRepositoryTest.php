<?php

namespace Tests\Unit;

use App\Models\DateException;
use App\Models\User;
use App\Repositories\DateExceptionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DateExceptionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected DateExceptionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new DateExceptionRepository();
    }

    public function test_create_and_find_by_id()
    {
        $user = User::factory()->create();

        $data = [
            'exception_date' => now()->toDateString(),
            'shift' => 'morning',
            'justification' => 'Motivo teste',
            'user_id' => $user->id,
        ];

        $created = $this->repository->create($data);

        $this->assertDatabaseHas('date_exceptions', $data);

        $found = $this->repository->findById($created->id);

        $this->assertNotNull($found);
        $this->assertEquals($created->id, $found->id);
        $this->assertEquals($user->id, $found->user_id);
    }

    public function test_update_changes_fields()
    {
        $exception = DateException::factory()->create(['shift' => 'morning', 'justification' => 'Old justification']);

        $updated = $this->repository->update($exception, ['shift' => 'afternoon', 'justification' => 'New justification']);

        $this->assertEquals('afternoon', $updated->shift);
        $this->assertEquals('New justification', $updated->justification);
        $this->assertDatabaseHas('date_exceptions', [
            'id' => $exception->id,
            'shift' => 'afternoon',
            'justification' => 'New justification',
        ]);
    }

    public function test_delete_removes_record()
    {
        $exception = DateException::factory()->create();

        $this->repository->delete($exception);

        $this->assertDatabaseMissing('date_exceptions', ['id' => $exception->id]);
    }

    public function test_all_returns_collection()
    {
        DateException::factory()->count(3)->create();

        $all = $this->repository->all();

        $this->assertCount(3, $all);
        $this->assertEquals('App\Models\DateException', get_class($all->first()));
    }
}
