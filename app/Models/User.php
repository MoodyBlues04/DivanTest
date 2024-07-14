<?php

namespace App\Models;

use App\Models\Builders\UserBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property ?string $email_verified_at
 * @property string $password
 *
 * @property-read BankAccount $bankAccount
 *
 * @method static UserBuilder query()
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const DEFAULT_NAME = 'test';

   public function newEloquentBuilder($query): UserBuilder
   {
       return new UserBuilder($query);
   }

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function bankAccount(): HasOne
    {
        return $this->hasOne(BankAccount::class, 'user_id');
    }

    public function openBankAccountOrGet(): BankAccount
    {
        /** @var BankAccount */
        return $this->bankAccount()->createOrFirst();
    }
}
