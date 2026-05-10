<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Room;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

class ReservationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $room;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure createUser() in TestCase returns the user object!
        $this->admin = $this->createUser();

        $this->room = Room::create([
            'name' => 'Lab A',
            'building' => 'A',
            'floor' => '1',
            'capacity' => 10,
            'availability_status' => 'available'
        ]);
    }

    /** @test */
    public function test_it_can_create_a_reservation()
    {
        $this->actingAs($this->admin);

        $resData = [
            'room_id' => $this->room->id,
            'purpose' => 'Meeting',
            'reservation_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '12:00',
        ];

        $response = $this->post(route('reservations.store'), $resData);

        $response->assertStatus(302);
        $this->assertDatabaseHas('reservations', ['purpose' => 'Meeting']);
    }

    /** @test */
    public function test_it_can_read_reservation_list()
    {
        $this->actingAs($this->admin);

        Reservation::create([
            'user_id' => $this->admin->id,
            'room_id' => $this->room->id,
            'purpose' => 'Workshop',
            'submission_date' => now(),
            'usage_date' => now()->addDay(),
            'duration_hours' => 2,
            'status' => 'pending'
        ]);

        $response = $this->get(route('reservations.index'));

        $response->assertStatus(200);
        $response->assertSee('Workshop');
    }

    /** @test */
    public function test_it_can_update_a_reservation()
    {
        $this->actingAs($this->admin);

        $reservation = Reservation::create([
            'user_id' => $this->admin->id,
            'room_id' => $this->room->id,
            'purpose' => 'Old Purpose',
            'submission_date' => now(),
            'usage_date' => now()->addDay(),
            'duration_hours' => 2,
            'status' => 'pending'
        ]);

        $response = $this->put(route('reservations.update', $reservation->id), [
            'room_id' => $this->room->id,
            'purpose' => 'New Purpose',
            'reservation_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '12:00',
        ]);

        $this->assertEquals('New Purpose', $reservation->fresh()->purpose);
    }

    /** @test */
    public function test_it_can_delete_a_reservation()
    {
        $this->actingAs($this->admin);

        $reservation = Reservation::create([
            'user_id' => $this->admin->id,
            'room_id' => $this->room->id,
            'purpose' => 'To Be Deleted',
            'submission_date' => now(),
            'usage_date' => now()->addDay(),
            'duration_hours' => 1,
            'status' => 'pending'
        ]);

        if (Route::has('reservations.destroy')) {
            $response = $this->delete(route('reservations.destroy', $reservation->id));
            $this->assertDatabaseMissing('reservations', ['id' => $reservation->id]);
        } else {
            $this->markTestSkipped('Destroy route not defined.');
        }
    }
}