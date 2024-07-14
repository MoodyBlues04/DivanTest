<?php

namespace App\Models;

use App\Models\Enums\Currencies;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property Currencies $name
 *
 * @property-read Collection $currencyAccounts
 */
class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    protected $casts = [
        'name' => Currencies::class,
    ];

    public function currencyAccounts(): HasMany
    {
        return $this->hasMany(CurrencyAccount::class, 'currency_id');
    }
}
