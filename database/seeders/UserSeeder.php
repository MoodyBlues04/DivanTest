<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public const DEFAULT_NAME = 'test';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->state(['name' => self::DEFAULT_NAME])->create();
    }
}
