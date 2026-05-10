<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BorrowerControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        // Create an admin user using your helper and log in
        $this->admin = $this->createUser();
        $this->actingAs($this->admin);
    }

    /** @test */
    public function test_admin_can_list_all_borrowers()
    {
        // Create a student manually
        User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@student.xyz.ac.id',
            'password' => bcrypt('password'),
            'account_type' => 'student',
            'identity_number' => '20210001',
        ]);

        $response = $this->get(route('borrowers.index'));

        $response->assertStatus(200);
        $response->assertSee('Budi Santoso');
        $response->assertSee('20210001');
    }

    /** @test */
    public function test_admin_can_store_a_new_borrower()
    {
        $data = [
            'name' => 'Siti Aminah',
            'email' => 'siti@lecturer.xyz.ac.id',
            'password' => 'password123',
            'account_type' => 'lecturer',
            'identity_number' => '19880002',
            'phone_number' => '08123456789', // ADD THIS LINE
        ];

        $response = $this->post(route('borrowers.store'), $data);

        $response->assertRedirect(route('borrowers.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'siti@lecturer.xyz.ac.id',
            'identity_number' => '19880002'
        ]);
    }

    /** @test */
    public function test_cannot_create_borrower_with_duplicate_identity_number()
    {
        User::create([
            'name' => 'Original User',
            'email' => 'original@test.com',
            'password' => bcrypt('password'),
            'account_type' => 'student',
            'identity_number' => 'DUPLICATE123',
            'phone_number' => '0811111111', // Add this
        ]);

        $data = [
            'name' => 'Imposter User',
            'email' => 'imposter@test.com',
            'password' => 'password123',
            'account_type' => 'student',
            'identity_number' => 'DUPLICATE123',
            'phone_number' => '0822222222', // Add this
        ];

        $response = $this->post(route('borrowers.store'), $data);
        $response->assertSessionHasErrors('identity_number');
    }

    /** @test */
    public function test_admin_can_delete_a_borrower()
    {
        $userToDelete = User::create([
            'name' => 'Temporary User',
            'email' => 'temp@test.com',
            'password' => bcrypt('password'),
            'account_type' => 'student',
            'identity_number' => 'TEMP999',
        ]);

        $response = $this->delete(route('borrowers.destroy', $userToDelete->id));

        $response->assertRedirect(route('borrowers.index'));
        $this->assertDatabaseMissing('users', ['id' => $userToDelete->id]);
    }
}