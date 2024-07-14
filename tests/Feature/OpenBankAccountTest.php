<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Enums\CurrencyName;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpenBankAccountTest extends TestCase
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
    }

    public function testSetMainCurrency(): void
    {
//        TODO pcov coverage

        $this->assertTrue($this->bankAccount->setMainCurrencyAccount(self::MAIN_CURRENCY));
        $this->assertNotNull($this->bankAccount->getMainCurrencyAccount());
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
            $this->assertEquals($amount, $this->bankAccount->getBalance(CurrencyName::tryFrom($name)));
        }
        $this->assertEquals(
            $this->currencyAmounts[self::MAIN_CURRENCY->value],
            $this->bankAccount->getBalance()
        );
    }
}
