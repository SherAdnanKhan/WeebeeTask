<?php

namespace App\Http\Repositories;

use App\Models\User;

class UserRepository
{
    /**
     * Find the first user matching the attributes or create a new user with the given values.
     *
     * @param array $attributes - Array of attributes to search for
     * @param array $values - Array of values for the new user
     * @return \App\Models\User - User model
     */
    public function firstOrCreate(array $attributes, array $values): \App\Models\User
    {
        return User::firstOrCreate($attributes, $values);
    }
}
