<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Room;
use App\Models\Equipment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class EquipmentStockTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        // Create an admin to handle room/equipment setup
        $this->admin = $this->createUser();
    }

    /** @test */
    public function test_it_fails_if_requested_quantity_is_not_available_due_to_other_reservations()
    {
        $this->actingAs($this->admin);

        // 1. Create TWO Rooms and 2 Cameras
        $room1 = Room::create([
            'name' => 'Lab A', 'building' => '1', 'floor' => '1', 'capacity' => 10, 'availability_status' => 'available'
        ]);
        // Create a second room for the second request
        $room2 = Room::create([
            'name' => 'Lab B', 'building' => '1', 'floor' => '1', 'capacity' => 10, 'availability_status' => 'available'
        ]);

        $cam1 = Equipment::create(['name' => 'Sony A7', 'category' => 'Camera', 'status' => 'available', 'code' => 'CAM-01']);
        $cam2 = Equipment::create(['name' => 'Sony A7', 'category' => 'Camera', 'status' => 'available', 'code' => 'CAM-02']);

        // 2. Room 1 books 1 camera
        $existingRes = Reservation::create([
            'user_id' => $this->admin->id,
            'room_id' => $room1->id, // Books Room A
            'purpose' => 'Existing Meeting',
            'submission_date' => now(),
            'usage_date' => Carbon::now()->addDay()->setTime(10, 0),
            'duration_hours' => 2,
            'status' => 'approved',
        ]);
        $existingRes->equipment()->attach($cam1->id);

        // 3. TRY to book Room B (No room conflict) but 2 cameras (Equipment conflict!)
        $data = [
            'room_id' => $room2->id, // Use Room B here!
            'purpose' => 'Second Request',
            'reservation_date' => Carbon::now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '12:00',
            'equipment_requests' => [
                'Sony A7' => 2
            ],
        ];

        $response = $this->post(route('reservations.store'), $data);

        // 4. Now this assertion should pass because the room was free, but the cams were not
        $response->assertSessionHas('error', 'Some requested equipment is unavailable for this time slot. Please reduce the quantity or change the time.');

        $this->assertEquals(1, Reservation::count());
    }

    /** @test */
    public function test_it_allows_booking_if_times_do_not_overlap()
    {
        $this->actingAs($this->admin);

        $room = Room::create(['name' => 'Lab B', 'building' => '1', 'floor' => '1', 'capacity' => 10, 'availability_status' => 'available']);
        $cam = Equipment::create([
            'name' => 'Sony A7',
            'category' => 'Camera',
            'status' => 'available',
            'code' => 'CAM-003'
        ]);
        // Existing reservation ends at 12:00
        $existingRes = Reservation::create([
            'user_id' => $this->admin->id,
            'room_id' => $room->id,
            'purpose' => 'Morning Session',
            'submission_date' => now(),
            'usage_date' => Carbon::now()->addDay()->setTime(10, 0),
            'duration_hours' => 2,
            'status' => 'approved',
        ]);
        $existingRes->equipment()->attach($cam->id);

        // Requesting the SAME camera at 13:00 should WORK
        $data = [
            'room_id' => $room->id,
            'purpose' => 'Afternoon Session',
            'reservation_date' => Carbon::now()->addDay()->format('Y-m-d'),
            'start_time' => '13:00',
            'end_time' => '15:00',
            'equipment_requests' => [
                'Sony A7' => 1
            ],
        ];

        $response = $this->post(route('reservations.store'), $data);

        $response->assertRedirect(route('reservations.index'));
        $this->assertEquals(2, Reservation::count());
    }
}