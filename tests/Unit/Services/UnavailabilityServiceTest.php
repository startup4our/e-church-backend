<?php

namespace Tests\Unit;

use App\Models\Unavailability;
use App\Repositories\UnavailabilityRepository;
use App\Services\UnavailabilityService;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class UnavailabilityServiceTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_success()
    {
        $repo = Mockery::mock(UnavailabilityRepository::class);
        $repo->shouldReceive('exists')->once()->andReturn(false);
        $repo->shouldReceive('create')->once()->andReturn(new Unavailability(['id' => 1]));

        $service = new UnavailabilityService($repo);

        $data = ['user_id' => 1, 'weekday' => 1, 'shift' => 'manha'];
        $result = $service->create($data);

        $this->assertInstanceOf(Unavailability::class, $result);
    }

    public function test_create_duplicate_throws_exception()
    {
        $this->expectException(ValidationException::class);

        $repo = Mockery::mock(UnavailabilityRepository::class);
        $repo->shouldReceive('exists')->once()->andReturn(true);

        $service = new UnavailabilityService($repo);
        $service->create(['user_id' => 1, 'weekday' => 1, 'shift' => 'manha']);
    }
}
