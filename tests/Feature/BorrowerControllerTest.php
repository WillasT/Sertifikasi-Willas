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
    public function test_borrower_lifecycle()
    {
        $this->actingAs($this->admin);

        // 1. CREATE
        $userData = [
            'name' => 'Budi',
            'email' => 'budi@student.com',
            'password' => 'password123',
            'identity_number' => 'NIM123',
            'phone_number' => '0812345678',
            'account_type' => 'student'
        ];
        $this->post(route('borrowers.store'), $userData);
        $user = \App\Models\User::where('email', 'budi@student.com')->first();

        // 2. READ
        $this->get(route('borrowers.index'))->assertSee('NIM123');

        // 3. UPDATE
        $this->patch(route('borrowers.update', $user->id), array_merge($userData, ['name' => 'Budi Updated']));
        $this->assertEquals('Budi Updated', $user->fresh()->name);

        // 4. DELETE
        $this->delete(route('borrowers.destroy', $user->id));
        $this->assertDatabaseMissing('users', ['email' => 'budi@student.com']);
    }
}