<?php

namespace Tests\Unit\Repositories;

use App\Models\Handout;
use App\Models\Church;
use App\Models\Area;
use App\Repositories\HandoutRepository;
use App\Enums\HandoutStatus;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HandoutRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new HandoutRepository();
    }

    public function test_retrieve_all_handouts_for_church()
    {
        $church1 = Church::factory()->create();
        $church2 = Church::factory()->create();

        Handout::factory()->count(3)->create(['church_id' => $church1->id]);
        Handout::factory()->count(2)->create(['church_id' => $church2->id]);
        Handout::factory()->create([
            'church_id' => $church1->id,
            'status' => HandoutStatus::DELETED->value
        ]);

        $handouts = $this->repository->all($church1->id);

        $this->assertCount(3, $handouts);
        $handouts->each(function ($handout) use ($church1) {
            $this->assertEquals($church1->id, $handout->church_id);
            $this->assertNotEquals(HandoutStatus::DELETED->value, $handout->status);
        });
    }

    public function test_filter_visible_handouts_by_area()
    {
        $church = Church::factory()->create();
        $area1 = Area::factory()->create(['church_id' => $church->id]);
        $area2 = Area::factory()->create(['church_id' => $church->id]);

        // Visible handout for area1
        Handout::factory()->create([
            'church_id' => $church->id,
            'area_id' => $area1->id,
            'status' => HandoutStatus::ACTIVE->value,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        // Visible handout for all areas (null area_id)
        Handout::factory()->create([
            'church_id' => $church->id,
            'area_id' => null,
            'status' => HandoutStatus::ACTIVE->value,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        // Invisible handout (inactive)
        Handout::factory()->create([
            'church_id' => $church->id,
            'area_id' => $area1->id,
            'status' => HandoutStatus::INACTIVE->value,
        ]);

        // Handout for different area
        Handout::factory()->create([
            'church_id' => $church->id,
            'area_id' => $area2->id,
            'status' => HandoutStatus::ACTIVE->value,
        ]);

        $handouts = $this->repository->getVisibleNow($church->id, [$area1->id]);

        $this->assertCount(2, $handouts);
        $this->assertTrue($handouts->contains(function ($handout) use ($area1) {
            return $handout->area_id === $area1->id || $handout->area_id === null;
        }));
    }

    public function test_create_handout_in_database()
    {
        $church = Church::factory()->create();
        
        $data = [
            'church_id' => $church->id,
            'title' => 'Test Handout',
            'description' => 'Test Description',
            'status' => HandoutStatus::PENDING->value,
            'priority' => 'high',
        ];

        $handout = $this->repository->create($data);

        $this->assertDatabaseHas('handouts', [
            'id' => $handout->id,
            'title' => 'Test Handout',
            'church_id' => $church->id,
            'status' => HandoutStatus::PENDING->value,
        ]);
    }

    public function test_update_handout_information()
    {
        $handout = Handout::factory()->create(['title' => 'Old Title']);

        $updated = $this->repository->update($handout, [
            'title' => 'Updated Title',
            'status' => HandoutStatus::ACTIVE->value
        ]);

        $this->assertEquals('Updated Title', $updated->title);
        $this->assertEquals(HandoutStatus::ACTIVE->value, $updated->status);
        $this->assertDatabaseHas('handouts', [
            'id' => $handout->id,
            'title' => 'Updated Title',
            'status' => HandoutStatus::ACTIVE->value
        ]);
    }

    public function test_mark_handout_as_deleted()
    {
        $handout = Handout::factory()->create([
            'status' => HandoutStatus::ACTIVE->value
        ]);

        $result = $this->repository->delete($handout);

        $this->assertTrue($result);
        $this->assertDatabaseHas('handouts', [
            'id' => $handout->id,
            'status' => HandoutStatus::DELETED->value
        ]);
        $this->assertDatabaseMissing('handouts', [
            'id' => $handout->id,
            'status' => HandoutStatus::ACTIVE->value
        ]);
    }
}

