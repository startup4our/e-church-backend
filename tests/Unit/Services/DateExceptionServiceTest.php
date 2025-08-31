<?php

namespace Tests\Unit;

use App\Models\DateException;
use App\Repositories\DateExceptionRepository;
use App\Services\DateExceptionService;
use Tests\TestCase;
use Mockery;

class DateExceptionServiceTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_exception()
    {
        $repo = Mockery::mock(DateExceptionRepository::class);
        $repo->shouldReceive('create')->once()->andReturn(new DateException(['id' => 1]));

        $service = new DateExceptionService($repo);

        $data = [
            'exception_date' => now()->toDateString(),
            'shift' => 'manha',
            'justification' => 'Test',
            'user_id' => 1,
        ];

        $exception = $service->create($data);

        $this->assertInstanceOf(DateException::class, $exception);
    }
}
