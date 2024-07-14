<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Currency;
use App\Models\CurrencyAccount;
use App\Models\Enums\CurrencyName;
use App\Models\ExchangeRate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankAccountTest extends TestCase
{
    use RefreshDatabase;

    private CurrencyName $mainCurrency = CurrencyName::RUB;

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
        $this->assertTrue($this->bankAccount->setMainCurrencyAccount($this->mainCurrency));
        $this->assertNotNull($this->bankAccount->getMainCurrencyAccount());
        $this->assertEquals($this->mainCurrency, $this->bankAccount->getMainCurrencyAccount()->currency->name);
    }

    public function testRecharge(): void
    {
        foreach ($this->currencyAmounts as $name => $amount) {
            $this->assertTrue($this->bankAccount->recharge($amount, CurrencyName::tryFrom($name)));
            $this->assertEquals($amount, $this->bankAccount->currencyBalance(CurrencyName::tryFrom($name)));
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
            $this->currencyAmounts[$this->mainCurrency->value],
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

    public function testTotalBalance(): void
    {
        $this->testSetMainCurrency();
        $this->testRecharge();

        $expected = 0;
        $destination = $this->bankAccount->getMainCurrencyAccount()->currency;
        foreach ($this->currencyAmounts as $name => $amount) {
            $source = Currency::query()->getByName(CurrencyName::tryFrom($name));
            $rate = ExchangeRate::query()->getExchangeRate($source, $destination);
            $expected += $amount * $rate;
        }
        $this->assertEquals($expected, $this->bankAccount->totalBalance());
    }

    public function testTotalBalanceWithAnotherMainCurrency(): void
    {
        $this->mainCurrency = CurrencyName::USD;
        $this->testTotalBalance();
    }

    public function testTransferSuccess(): void
    {
        $this->testSetMainCurrency();
        $this->testRecharge();

        $currencyNames = array_map(
            fn (string $name) => CurrencyName::tryFrom($name),
            array_keys($this->currencyAmounts)
        );
        $source = fake()->randomElement($currencyNames);
        $destination = fake()->randomElement($currencyNames);

        $sourceAmount = $this->bankAccount->currencyBalance($source);
        $transferAmount = fake()->numberBetween(1, $sourceAmount);

        $expectedTotalBalance = $this->bankAccount->totalBalance();
        $this->assertTrue($this->bankAccount->transfer($source, $destination, $transferAmount));
        $this->assertEquals($expectedTotalBalance, $this->bankAccount->totalBalance());
    }

    public function testTransferFail(): void
    {
        $this->testSetMainCurrency();
        $this->testRecharge();

        $currencyNames = $this->getCurrencyNames();
        $source = fake()->randomElement($currencyNames);
        $destination = fake()->randomElement($currencyNames);

        $sourceAmount = $this->bankAccount->currencyBalance($source);
        $transferAmount = fake()->numberBetween($sourceAmount, $sourceAmount + 1000);

        $expectedTotalBalance = $this->bankAccount->totalBalance();
        $this->expectException(\LogicException::class);
        $this->bankAccount->transfer($source, $destination, $transferAmount);
        $this->assertEquals($expectedTotalBalance, $this->bankAccount->totalBalance());
    }

    public function testCurrencyRemoveSuccess()
    {
        $this->testSetMainCurrency();
        $this->testRecharge();

        $currencyNames = array_filter(
            $this->getCurrencyNames(),
            fn (CurrencyName $name) => $name !== $this->mainCurrency
        );
        $source = fake()->randomElement($currencyNames);

        $expectedTotalBalance = $this->bankAccount->totalBalance();
        $this->bankAccount->removeCurrency($source);
        $this->assertEquals($expectedTotalBalance, $this->bankAccount->totalBalance());
    }

    public function testCurrencyRemoveFail()
    {
        $this->testSetMainCurrency();
        $this->testRecharge();

        $expectedTotalBalance = $this->bankAccount->totalBalance();
        $this->expectException(\LogicException::class);
        $this->bankAccount->removeCurrency($this->mainCurrency);
        $this->assertEquals($expectedTotalBalance, $this->bankAccount->totalBalance());
    }

    private function getCurrencyNames(): array
    {
        return array_map(
            fn (string $name) => CurrencyName::tryFrom($name),
            array_keys($this->currencyAmounts)
        );
    }
}
