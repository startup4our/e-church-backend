<?php

namespace Tests\Unit\Services;

use App\Models\Handout;
use App\Repositories\HandoutRepository;
use App\Services\HandoutService;
use App\Enums\HandoutStatus;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class HandoutServiceTest extends TestCase
{
    use RefreshDatabase;

    private $repositoryMock;
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(HandoutRepository::class);
        $this->service = new HandoutService($this->repositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_update_validates_status()
    {
        $handout = Handout::factory()->create();
        
        $data = [
            'id' => $handout->id,
            'status' => 'INVALID'
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Status inválido.');
        
        $this->service->update($data);
    }

    public function test_update_handout_successfully()
    {
        $handout = Handout::factory()->create(['title' => 'Old Title']);

        $data = [
            'id' => $handout->id,
            'title' => 'New Title',
            'status' => HandoutStatus::ACTIVE->value
        ];

        $updatedHandout = clone $handout;
        $updatedHandout->title = 'New Title';
        $updatedHandout->status = HandoutStatus::ACTIVE->value;

        $this->repositoryMock
            ->shouldReceive('update')
            ->once()
            ->with(Mockery::type(Handout::class), Mockery::on(function ($arg) {
                return $arg['title'] === 'New Title'
                    && $arg['status'] === HandoutStatus::ACTIVE->value
                    && !isset($arg['id']);
            }))
            ->andReturn($updatedHandout);

        $result = $this->service->update($data);

        $this->assertInstanceOf(Handout::class, $result);
    }

    public function test_delete_sets_inactive_status()
    {
        $handout = Handout::factory()->create();

        $this->repositoryMock
            ->shouldReceive('delete')
            ->once()
            ->with(Mockery::type(Handout::class))
            ->andReturn(true);

        $result = $this->service->delete(['id' => $handout->id]);

        $this->assertTrue($result);
    }
}

