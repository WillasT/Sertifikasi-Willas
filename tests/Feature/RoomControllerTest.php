<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoomControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure createUser() is defined in your tests/TestCase.php
        $this->user = $this->createUser();
        $this->actingAs($this->user);
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
}