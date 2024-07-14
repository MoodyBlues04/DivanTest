<?php

namespace App\Models\Builders;

use App\Models\CurrencyAccount;
use App\Models\Enums\CurrencyName;
use Illuminate\Database\Eloquent\Builder;

class CurrencyAccountBuilder extends Builder
{
    public function getByNameOrFail(CurrencyName $name): ?CurrencyAccount
    {
        $currencyAccount = $this->getByName($name);
        if (null === $currencyAccount) {
            throw new \InvalidArgumentException("Currency account not found for: {$name->value}");
        }
        return $currencyAccount;
    }

    public function getByName(CurrencyName $name): ?CurrencyAccount
    {
        /** @var ?CurrencyAccount */
        return $this->whereHas('currency', function (Builder $q) use ($name) {
            return $q->where('name', $name);
        })->first();
    }

    public function main(): self
    {
        return $this->where('is_main', true);
    }
}
