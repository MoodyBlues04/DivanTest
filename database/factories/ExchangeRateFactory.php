<?php

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExchangeRate>
 */
class ExchangeRateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $currenciesIds = Currency::query()->get()->map(fn (Currency $currency) => $currency->id)->all();
        return [
            'rate' => fake()->numberBetween(100, 10000),
            'source_currency_id' => fake()->randomElement($currenciesIds),
            'destination_currency_id' => fake()->randomElement($currenciesIds),
        ];
    }
}
