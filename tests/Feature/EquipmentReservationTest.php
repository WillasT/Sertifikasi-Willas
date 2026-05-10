<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Room;
use App\Models\Equipment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EquipmentStockTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->createUser(); // Using your 'admin' helper
    }

    /** @test */
    public function test_admin_can_store_equipment_with_stock()
    {
        $this->actingAs($this->admin);

        $data = [
            'name' => 'Projector Epson EB-X400',
            'stock' => 5,
            'category' => 'Electronics',
            'condition' => 'good'
        ];

        $response = $this->post(route('equipments.store'), $data);

        $response->assertRedirect(route('equipments.index'));
        $this->assertDatabaseHas('equipments', [
            'name' => 'Projector Epson EB-X400',
            'stock' => 5
        ]);
    }

    /** @test */
    public function test_cannot_reserve_more_equipment_than_available_stock()
    {
        $this->actingAs($this->admin);

        // 1. Create a Room
        $room = Room::create([
            'name' => 'Meeting Room 1',
            'building' => 'A',
            'floor' => '1',
            'capacity' => 10,
            'availability_status' => 'available'
        ]);

        // 2. Create Equipment with 2 items in stock
        $equipment = Equipment::create([
            'name' => 'Laptop Dell',
            'stock' => 2,
            'category' => 'Electronics'
        ]);

        // 3. Attempt to reserve 5 items (which is > stock of 2)
        $reservationData = [
            'room_id' => $room->id,
            'usage_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '11:00',
            'purpose' => 'Testing stock logic',
            'equipment_ids' => [$equipment->id],
            'quantities' => [5] // THE FAIL POINT
        ];

        $response = $this->post(route('reservations.store'), $reservationData);

        // 4. Assert it failed validation for the quantity
        $response->assertSessionHasErrors('quantities.0');
    }
}