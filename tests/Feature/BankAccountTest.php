<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Currency;
use App\Models\Enums\CurrencyName;
use App\Models\ExchangeRate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankAccountTest extends TestCase
{
    use RefreshDatabase;

    private const MAIN_CURRENCY = CurrencyName::RUB;

    private ?User $user = null;
    private ?BankAccount $bankAccount = null;
    private array $currencyAmounts = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::query()->getByName(User::DEFAULT_NAME);
        $this->bankAccount = $this->user->openBankAccountOrGet();

        $this->currencyAmounts = [];
        foreach (CurrencyName::cases() as $name) {
            $this->currencyAmounts[$name->value] = fake()->numberBetween(1, 1000);
        }
        foreach ($this->currencyAmounts as $name => $amount) {
            $this->bankAccount->addCurrency(CurrencyName::tryFrom($name));
        }
        foreach ($this->bankAccount->getCurrencyNames() as $currencyName) {
            $this->assertTrue(in_array($currencyName->value, array_keys($this->currencyAmounts)));
        }

//        TODO pcov coverage
    }

    public function testSetMainCurrency(): void
    {
        $this->assertTrue($this->bankAccount->setMainCurrencyAccount(self::MAIN_CURRENCY));
        $this->assertNotNull($this->bankAccount->getMainCurrencyAccount());
        $this->assertEquals(self::MAIN_CURRENCY, $this->bankAccount->getMainCurrencyAccount()->currency->name);
    }

    public function testRecharge(): void
    {
        foreach ($this->currencyAmounts as $name => $amount) {
            $this->assertTrue($this->bankAccount->recharge($amount, CurrencyName::tryFrom($name)));
        }
    }

    public function testGetBalance(): void
    {
        $this->testSetMainCurrency();
        $this->testRecharge();

        foreach ($this->currencyAmounts as $name => $amount) {
            $this->assertEquals($amount, $this->bankAccount->currencyBalance(CurrencyName::tryFrom($name)));
        }
        $this->assertEquals(
            $this->currencyAmounts[self::MAIN_CURRENCY->value],
            $this->bankAccount->currencyBalance()
        );
    }

    public function testChargeSuccess(): void
    {
        $this->testRecharge();

        foreach ($this->currencyAmounts as $name => $amount) {
            $currencyName = CurrencyName::tryFrom($name);
            $toPay = fake()->numberBetween(0, $amount);
            $expected = $amount - $toPay;
            $this->assertTrue($this->bankAccount->charge($toPay, $currencyName));
            $this->assertEquals($expected, $this->bankAccount->currencyBalance($currencyName));
        }
    }

    public function testChargeNotEnoughMoney(): void
    {
        $this->testRecharge();

        foreach ($this->currencyAmounts as $name => $amount) {
            $currencyName = CurrencyName::tryFrom($name);
            $toPay = fake()->numberBetween($amount + 1, $amount + 1000);
            $this->expectException(\LogicException::class);
            $this->assertTrue($this->bankAccount->charge($toPay, $currencyName));
        }
    }

    public function testChangeExchangeRate(): void
    {
        $source = Currency::query()->getByName(CurrencyName::USD);
        $destination = Currency::query()->getByName(CurrencyName::RUB);
        $rate = fake()->numberBetween(1, 1000);

        $this->assertTrue(ExchangeRate::query()->setExchangeRate($source, $destination, $rate));
        $this->assertEquals($rate, ExchangeRate::query()->getExchangeRate($source, $destination));
    }
}
