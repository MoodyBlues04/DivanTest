<?php

namespace App\Models\Builders;

use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Database\Eloquent\Builder;

class ExchangeRateBuilder extends Builder
{
    public function getExchangeRate(Currency $source, Currency $destination): float
    {
        /** @var ExchangeRate $exchangeRate */
        $exchangeRate = ExchangeRate::query()->whereCurrency($source, $destination)->first();
        if (null !== $exchangeRate) {
            return (float)$exchangeRate->rate;
        }
        /** @var ExchangeRate $exchangeRate */
        $exchangeRate = $this->whereCurrency($destination, $source)->first();
        if (null === $exchangeRate) {
            throw new \LogicException("Cannot convert {$source->name->value} to {$destination->name->value}");
        }
        return 1 / (float)$exchangeRate->rate;
    }

    public function setExchangeRate(Currency $source, Currency $destination, int $rate): bool
    {
        ExchangeRate::query()->whereCurrency($destination, $source)->delete();
        return (bool)$this->updateOrCreate([
            'source_currency_id' => $source->id,
            'destination_currency_id' => $destination->id,
        ], ['rate' => $rate]);
    }

    public function whereCurrency(Currency $source, Currency $destination): self
    {
        return $this->where('source_currency_id', $source->id)
                ->where('destination_currency_id', $destination->id);
    }
}
