<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\User;

abstract class TestCase extends BaseTestCase
{
    protected function createUser()
    {
        return User::create([
            'name' => 'Admin User',
            'email' => 'admin' . rand() . '@test.com',
            'password' => bcrypt('password'),
            // CHANGE THIS TO ADMIN:
            'account_type' => 'admin',
            'identity_number' => '12345678',
        ]);
    }
}