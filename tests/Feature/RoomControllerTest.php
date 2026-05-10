<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoomControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // You assigned it to $this->admin here...
        $this->admin = $this->createUser();

        // ...so you MUST use $this->admin here!
        $this->actingAs($this->admin);
    }

    public function test_it_can_list_all_rooms()
    {
        Room::create([
            'name' => 'Lab Multimedia',
            'building' => 'Building A',
            'floor' => '2',
            'capacity' => 30,
            'availability_status' => 'available',
        ]);

        $response = $this->get(route('rooms.index'));

        $response->assertStatus(200);
        $response->assertSee('Lab Multimedia');
    }

    public function test_it_can_store_a_valid_room()
    {
        $data = [
            'name' => 'Seminar Room 1',
            'building' => 'Gedung Rektorat',
            'floor' => '1',
            'capacity' => 50,
            'availability_status' => 'available',
        ];

        $response = $this->post(route('rooms.store'), $data);

        $response->assertRedirect(route('rooms.index'));
        $this->assertDatabaseHas('rooms', ['name' => 'Seminar Room 1']);
    }

    public function test_it_fails_if_capacity_is_zero_or_negative()
    {
        $data = [
            'name' => 'Invalid Room',
            'building' => 'Building B',
            'floor' => '1',
            'capacity' => 0,
            'availability_status' => 'available',
        ];

        $response = $this->post(route('rooms.store'), $data);

        $response->assertSessionHasErrors('capacity');
    }

    /** @test */
    public function test_admin_can_update_room_details()
    {
        // If $this->admin is null, create it inline to be safe
        $admin = $this->admin ?? $this->createUser();
        $this->actingAs($admin);

        $room = Room::create([
            'name' => 'Old Lab',
            'building' => 'A',
            'floor' => '1',
            'capacity' => 20,
            'availability_status' => 'available'
        ]);

        // Make sure your route uses 'put' or 'patch'
        $response = $this->put(route('rooms.update', $room->id), [
            'name' => 'Updated Lab',
            'building' => 'B',
            'floor' => '2',
            'capacity' => 30,
            'availability_status' => 'available'
        ]);

        $response->assertRedirect(route('rooms.index'));
        $this->assertEquals('Updated Lab', $room->fresh()->name);
    }

    /** @test */
    public function test_admin_can_delete_room()
    {
        $admin = $this->admin ?? $this->createUser();
        $this->actingAs($admin);

        $room = Room::create([
            'name' => 'Delete Me',
            'building' => 'X',
            'floor' => '1',
            'capacity' => 5,
            'availability_status' => 'available'
        ]);

        $response = $this->delete(route('rooms.destroy', $room->id));

        $response->assertRedirect(route('rooms.index'));
        $this->assertDatabaseMissing('rooms', ['id' => $room->id]);
    }
}
