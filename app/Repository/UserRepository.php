<?php

namespace App\Repository;

use App\Interface\Repository\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
    public function findByEmail(string $email)
    {
        return User::where('email', $email)->first();
    }

    public function create(object $payload)
    {
        $user = new User;
        $user->email = $payload->email;
        $user->password = Hash::make($payload->password);
        $user->firstname = $payload->firstname;
        $user->lastname = $payload->lastname;
        $user->is_active = $payload->is_active ?? true;
        $user->save();

        return $user->fresh();
    }
}
