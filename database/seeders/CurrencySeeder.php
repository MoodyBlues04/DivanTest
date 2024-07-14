<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Enums\Currencies;
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
                ['name' => Currencies::RUB],
                ['name' => Currencies::USD],
                ['name' => Currencies::EUR],
            ))->create();

        $currenciesIds = $currencies->reduce(function (array $carry, Currency $item) {
            $carry[$item->name->name] = $item->id;
            return $carry;
        }, []);

        $rates = [
            [
                'source' => Currencies::USD->name,
                'destination' => Currencies::RUB->name,
                'rate' => 70 * 100,
            ],
            [
                'source' => Currencies::EUR->name,
                'destination' => Currencies::RUB->name,
                'rate' => 80 * 100,
            ],
            [
                'source' => Currencies::USD->name,
                'destination' => Currencies::EUR->name,
                'rate' => 1 * 100,
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
