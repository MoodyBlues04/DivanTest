<?php

namespace App\Models;

use App\Models\Builders\CurrencyAccountBuilder;
use App\Models\Enums\CurrencyName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $amount stored multiplied by 100, because of double inaccuracy
 * @property bool $is_main
 * @property int $bank_account_id
 * @property int $currency_id
 *
 * @property-read BankAccount $bankAccount
 * @property-read Currency $currency
 *
 * @method static CurrencyAccountBuilder query()
 */
class CurrencyAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'is_main',
        'bank_account_id',
        'currency_id',
    ];

    public function newEloquentBuilder($query): CurrencyAccountBuilder
    {
        return new CurrencyAccountBuilder($query);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    /**
     * It's really not good to exchange currencies into float, because of precision problems
     * @param CurrencyName $name
     * @return float
     */
    public function exchangeTo(CurrencyName $name): float
    {
        $source = $this->currency;
        $destination = Currency::query()->getByName($name);
        $rate = ExchangeRate::query()->getExchangeRate($source, $destination);
        return $this->amount * $rate;
    }
}
