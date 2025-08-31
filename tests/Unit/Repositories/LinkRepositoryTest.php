<?php

namespace Tests\Unit\Repositories;

use App\Models\Link;
use App\Models\Song;
use App\Repositories\LinkRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinkRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected LinkRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new LinkRepository();
    }

    public function test_create_stores_link_in_database()
    {
        $song = Song::factory()->create();

        $data = [
            'name' => 'Test Link',
            'destination' => 'https://example.com',
            'description' => 'A test link description',
            'song_id' => $song->id
        ];

        $created = $this->repository->create($data);

        $this->assertInstanceOf(Link::class, $created);
        $this->assertDatabaseHas('links', $data);
        $this->assertEquals('Test Link', $created->name);
        $this->assertEquals('https://example.com', $created->destination);
        $this->assertEquals($song->id, $created->song_id);
    }

    public function test_get_all_returns_collection()
    {
        $song = Song::factory()->create();
        Link::factory()->count(3)->create(['song_id' => $song->id]);

        $all = $this->repository->getAll();

        $this->assertCount(3, $all);
        $this->assertInstanceOf(Link::class, $all->first());
        $this->assertEquals(Link::class, get_class($all->first()));
    }

    public function test_get_by_id_returns_link()
    {
        $song = Song::factory()->create();
        $link = Link::factory()->create([
            'name' => 'Specific Link',
            'song_id' => $song->id
        ]);

        $found = $this->repository->getById($link->id);

        $this->assertInstanceOf(Link::class, $found);
        $this->assertEquals($link->id, $found->id);
        $this->assertEquals('Specific Link', $found->name);
    }

    public function test_get_by_id_throws_exception_for_nonexistent_link()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->repository->getById(999);
    }

    public function test_update_modifies_link()
    {
        $song = Song::factory()->create();
        $link = Link::factory()->create([
            'name' => 'Old Name',
            'destination' => 'https://old.com',
            'song_id' => $song->id
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'destination' => 'https://updated.com'
        ];

        $updated = $this->repository->update($link->id, $updateData);

        $this->assertInstanceOf(Link::class, $updated);
        $this->assertEquals('Updated Name', $updated->name);
        $this->assertEquals('https://updated.com', $updated->destination);
        $this->assertDatabaseHas('links', [
            'id' => $link->id,
            'name' => 'Updated Name',
            'destination' => 'https://updated.com'
        ]);
    }

    public function test_update_throws_exception_for_nonexistent_link()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->repository->update(999, ['name' => 'Test']);
    }

    public function test_delete_removes_link()
    {
        $song = Song::factory()->create();
        $link = Link::factory()->create(['song_id' => $song->id]);

        $result = $this->repository->delete($link->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('links', ['id' => $link->id]);
    }

    public function test_delete_throws_exception_for_nonexistent_link()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->repository->delete(999);
    }

    public function test_create_link_with_nullable_description()
    {
        $song = Song::factory()->create();

        $data = [
            'name' => 'Link Without Description',
            'destination' => 'https://example.com',
            'description' => null,
            'song_id' => $song->id
        ];

        $created = $this->repository->create($data);

        $this->assertInstanceOf(Link::class, $created);
        $this->assertNull($created->description);
        $this->assertDatabaseHas('links', $data);
    }

    public function test_get_all_returns_empty_collection_when_no_links()
    {
        $all = $this->repository->getAll();

        $this->assertCount(0, $all);
        $this->assertEmpty($all);
    }
}
