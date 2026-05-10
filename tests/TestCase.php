<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\User;

abstract class TestCase extends BaseTestCase
{
    public function createUser()
    {
        // If you want to use your Seeder instead of a Factory:
        $this->artisan('db:seed', ['--class' => 'DatabaseSeeder']);
        return \App\Models\User::latest()->first();
    }
}
