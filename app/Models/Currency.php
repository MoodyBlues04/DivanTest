<?php

namespace App\Models;

use App\Models\Builders\CurrencyBuilder;
use App\Models\Enums\CurrencyName;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property CurrencyName $name
 *
 * @property-read Collection $currencyAccounts
 *
 * @method static CurrencyBuilder query()
 */
class Currency extends Model
{
    use HasFactory;

    public function newEloquentBuilder($query): CurrencyBuilder
    {
        return new CurrencyBuilder($query);
    }

    protected $fillable = [
        'name',
    ];

    protected $casts = [
        'name' => CurrencyName::class,
    ];

    public function currencyAccounts(): HasMany
    {
        return $this->hasMany(CurrencyAccount::class, 'currency_id');
    }
}
