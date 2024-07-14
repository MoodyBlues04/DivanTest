<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Currency;
use App\Models\Enums\CurrencyName;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpenBankAccountTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Клиент открывает мультивалютный счет, включающий сбережения в 3-х валютах с
     * основной валютой российский рубль, и пополняет его случайными суммами.
     *
     * @return void
     */
    public function test(): void
    {

        $this->seed();

//        TODO вынести всю логику в модели/репы
//        TODO pcov coverage

        $user = User::query()->getByName(User::DEFAULT_NAME);
        $this->assertNotNull($user);

        $bankAccount = $user->openBankAccountOrGet();

        $currencyNames = CurrencyName::cases();
        foreach ($currencyNames as $currencyName) {
            $rubCurrency = $bankAccount->addCurrency($currencyName);
            $this->assertNotNull($rubCurrency);
        }

        $this->assertTrue($bankAccount->setMainCurrencyAccount(CurrencyName::RUB));
        $this->assertNotNull($bankAccount->getMainCurrencyAccount());

        $this->assertEquals($currencyNames, $bankAccount->getCurrencyNames());

        foreach ($currencyNames as $currencyName) {
            $amount = fake()->numberBetween(1, 1000);
            $this->assertTrue($bankAccount->recharge($amount, $currencyName));
        }
    }
}
