<?php

namespace App\Models;

use App\Models\Builders\CurrencyAccountBuilder;
use App\Models\Enums\CurrencyName;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 *
 * @property-read User $user
 * @property-read Collection $currencyAccounts
 */
class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function currencyAccounts(): HasMany
    {
        return $this->hasMany(CurrencyAccount::class, 'bank_account_id');
    }

    public function currencyAccountsBuilder(): CurrencyAccountBuilder
    {
        return (new CurrencyAccountBuilder($this->currencyAccounts()->getQuery()->getQuery()))
            ->setModel($this->currencyAccounts()->getQuery()->getModel());
    }

    public function addCurrency(CurrencyName $currencyName): CurrencyAccount
    {
        $currency = Currency::query()->getByName($currencyName);
        if (null === $currency) {
            throw new \InvalidArgumentException("Invalid currency name: $currencyName->value");
        }
        /** @var CurrencyAccount */
        return $this->currencyAccounts()->firstOrCreate(['currency_id' => $currency->id]);
    }

    /**
     * @return CurrencyName[]
     */
    public function getCurrencyNames(): array
    {
        return $this->currencyAccounts
            ->map(fn (CurrencyAccount $currencyAccount) => $currencyAccount->currency->name)
            ->all();
    }

    public function getMainCurrencyAccount(): ?CurrencyAccount
    {
        /** @var ?CurrencyAccount */
        return $this->currencyAccountsBuilder()->main()->first();
    }

    public function setMainCurrencyAccount(CurrencyName $currencyName): bool
    {
        $currencyAccount = $this->currencyAccountsBuilder()->getByNameOrFail($currencyName);
        $this->currencyAccountsBuilder()->main()->update(['is_main' => false]);
        $currencyAccount->is_main = true;
        return $currencyAccount->save();
    }

    public function recharge(int $amount, CurrencyName $currencyName): bool
    {
        $currencyAccount = $this->currencyAccountsBuilder()->getByNameOrFail($currencyName);
        $currencyAccount->amount += $amount;
        return $currencyAccount->save();
    }

    public function charge(int $amount, CurrencyName $currencyName): bool
    {
        $currencyAccount = $this->currencyAccountsBuilder()->getByNameOrFail($currencyName);
        if ($currencyAccount->amount < $amount) {
            throw new \LogicException("Not enough money to charge in currency: $currencyName->value");
        }
        $currencyAccount->amount -= $amount;
        return $currencyAccount->save();
    }

    public function balance(?CurrencyName $currencyName = null): int
    {
        if (null === $currencyName) {
            $currencyAccount = $this->getMainCurrencyAccount();
            if (null === $currencyAccount) {
                throw new \LogicException('Main currency not set');
            }
        } else {
            $currencyAccount = $this->currencyAccountsBuilder()->getByNameOrFail($currencyName);
        }
        return $currencyAccount->amount;
    }
}
