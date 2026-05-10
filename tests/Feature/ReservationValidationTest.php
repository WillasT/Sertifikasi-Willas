<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Room;
use App\Models\Equipment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class ReservationValidationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $room;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser();
        $this->room = Room::create([
            'name' => 'Studio 1',
            'building' => 'A',
            'floor' => '1',
            'capacity' => 5,
            'availability_status' => 'available'
        ]);
    }

    /** @test */
    public function test_cannot_submit_empty_data()
    {
        $this->actingAs($this->user);

        // Sending an empty array to the store route
        $response = $this->post(route('reservations.store'), []);

        // Should return validation errors for required fields
        $response->assertSessionHasErrors(['room_id', 'purpose', 'reservation_date', 'start_time', 'end_time']);
    }

    /** @test */
    public function test_cannot_request_more_than_available_physical_stock()
    {
        $this->actingAs($this->user);

        // Create only 1 physical camera
        Equipment::create([
            'name' => 'Tripod', 'category' => 'Accessory', 'status' => 'available', 'code' => 'TRP-01'
        ]);

        $data = [
            'room_id' => $this->room->id,
            'purpose' => 'Photo Session',
            'reservation_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '12:00',
            'equipment_requests' => [
                'Tripod' => 5 // Asking for 5 when only 1 exists
            ]
        ];

        $response = $this->post(route('reservations.store'), $data);

        // Should trigger the custom error message we added to the controller
        $response->assertSessionHas('error', 'Some requested equipment is unavailable for this time slot. Please reduce the quantity or change the time.');
        $this->assertEquals(0, Reservation::count());
    }

    /** @test */
    public function test_cannot_reserve_overlapping_room_time()
    {
        $this->actingAs($this->user);

        $date = now()->addDay()->format('Y-m-d');

        // 1. Create an existing approved reservation
        Reservation::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'purpose' => 'Existing Class',
            'submission_date' => now(),
            'usage_date' => Carbon::parse($date . ' 09:00'),
            'duration_hours' => 2, // Ends at 11:00
            'status' => 'approved',
        ]);

        // 2. Attempt to book at 10:00 (Overlap!)
        $data = [
            'room_id' => $this->room->id,
            'purpose' => 'Crashing Class',
            'reservation_date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:00',
        ];

        $response = $this->post(route('reservations.store'), $data);

        $response->assertSessionHas('error', 'This room is already booked.');
        $this->assertEquals(1, Reservation::count()); // Only the first one should exist
    }
}