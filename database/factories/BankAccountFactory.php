<?php

namespace Database\Factories;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BankAccount>
 */
class BankAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::query()->where('name', UserSeeder::DEFAULT_NAME)->first()
            ?? User::factory()->create();

        return [
            'user_id' => $user->id,
        ];
    }
}
