<?php

namespace App\Models\Builders;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UserBuilder extends Builder
{
    public function getByName(string $name): ?User
    {
        /** @var ?User */
        return $this->where('name', $name)->first();
    }
}
