<?php

namespace App\Models;

use App\Models\Builders\ExchangeRateBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $source_currency_id
 * @property int $destination_currency_id
 * @property int $rate
 *
 * @property-read Currency $source
 * @property-read Currency $destination
 *
 * @method static ExchangeRateBuilder query()
 */
class ExchangeRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_currency_id',
        'destination_currency_id',
        'rate',
    ];

    public function newEloquentBuilder($query): ExchangeRateBuilder
    {
        return new ExchangeRateBuilder($query);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'source_currency_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'destination_currency_id');
    }
}
