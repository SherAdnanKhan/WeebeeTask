<?php

namespace App\Http\Repositories;

use App\Models\User;

class UserRepository
{
    public function firstOrCreate(array $attributes, array $values)
    {
        return User::firstOrCreate($attributes, $values);
    }
}