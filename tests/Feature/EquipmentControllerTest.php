<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Equipment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EquipmentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        // Uses your helper from TestCase.php
        $this->admin = $this->createUser();
    }

    /** @test */
    public function test_it_can_create_new_equipment()
    {
        $this->actingAs($this->admin);

        $data = [
            'code' => 'CAM-001',
            'name' => 'Sony A7',
            'category' => 'Camera',
            'status' => 'available',
        ];

        $response = $this->post(route('equipments.store'), $data);

        $response->assertRedirect(route('equipments.index'));
        $this->assertDatabaseHas('equipment', [
            'code' => 'CAM-001',
            'name' => 'Sony A7'
        ]);
    }

    /** @test */
    public function test_it_can_read_equipment_list_grouped()
    {
        $this->actingAs($this->admin);

        // Create two items of the same type to test your grouping logic in index()
        Equipment::create(['code' => 'PRJ-01', 'name' => 'Epson', 'category' => 'Projector', 'status' => 'available']);
        Equipment::create(['code' => 'PRJ-02', 'name' => 'Epson', 'category' => 'Projector', 'status' => 'available']);

        $response = $this->get(route('equipments.index'));

        $response->assertStatus(200);
        // Check if the name appears in the view
        $response->assertSee('Epson');
    }

    /** @test */
    public function test_it_can_update_equipment_details()
    {
        $this->actingAs($this->admin);

        $equipment = Equipment::create([
            'code' => 'LPT-01',
            'name' => 'Dell XPS',
            'category' => 'Laptop',
            'status' => 'available'
        ]);

        $updatedData = [
            'code' => 'LPT-01', // Keep same code
            'name' => 'Dell XPS Pro', // Update name
            'category' => 'Laptop',
            'status' => 'maintenance', // Change status
        ];

        $response = $this->put(route('equipments.update', $equipment->id), $updatedData);

        $response->assertRedirect(route('equipments.index'));
        $this->assertEquals('maintenance', $equipment->fresh()->status);
        $this->assertEquals('Dell XPS Pro', $equipment->fresh()->name);
    }

    /** @test */
    public function test_it_can_delete_equipment()
    {
        $this->actingAs($this->admin);

        $equipment = Equipment::create([
            'code' => 'MIC-99',
            'name' => 'Rode Mic',
            'category' => 'Audio',
            'status' => 'available'
        ]);

        $response = $this->delete(route('equipments.destroy', $equipment->id));

        $response->assertRedirect(route('equipments.index'));
        $this->assertDatabaseMissing('equipment', ['code' => 'MIC-99']);
    }
}