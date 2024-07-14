<?php

namespace App\Models\Builders;

use App\Models\Currency;
use App\Models\Enums\CurrencyName;
use Illuminate\Database\Eloquent\Builder;

class CurrencyBuilder extends Builder
{
    public function getByName(CurrencyName $name): ?Currency
    {
        /** @var ?Currency */
        return $this->where('name', $name)->first();
    }
}
