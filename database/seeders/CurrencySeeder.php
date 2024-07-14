<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Enums\CurrencyName;
use App\Models\ExchangeRate;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = Currency::factory()
            ->count(3)
            ->state(new Sequence(
                ['name' => CurrencyName::RUB],
                ['name' => CurrencyName::USD],
                ['name' => CurrencyName::EUR],
            ))->create();

        $currenciesIds = $currencies->reduce(function (array $carry, Currency $item) {
            $carry[$item->name->name] = $item->id;
            return $carry;
        }, []);

        $rates = [
            [
                'source' => CurrencyName::USD->name,
                'destination' => CurrencyName::RUB->name,
                'rate' => 70,
            ],
            [
                'source' => CurrencyName::EUR->name,
                'destination' => CurrencyName::RUB->name,
                'rate' => 80,
            ],
            [
                'source' => CurrencyName::USD->name,
                'destination' => CurrencyName::EUR->name,
                'rate' => 1,
            ],
        ];

        ExchangeRate::factory()
            ->count(3)
            ->state(new Sequence(...array_map(function (array $item) use ($currenciesIds) {
                return [
                    'source_currency_id' => $currenciesIds[$item['source']],
                    'destination_currency_id' => $currenciesIds[$item['destination']],
                    'rate' => $item['rate'],
                ];
            }, $rates)))->create();
    }
}
