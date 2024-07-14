<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $source_currency_id
 * @property int $destination_currency_id
 * @property int $rate stored multiplied by 100, because of double inaccuracy
 *
 * @property-read Currency $source
 * @property-read Currency $destination
 */
class ExchangeRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_currency_id',
        'destination_currency_id',
        'rate',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'source_currency_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'destination_currency_id');
    }
}
