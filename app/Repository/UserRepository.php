<?php

namespace App\Repository;

use App\Interface\Repository\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
    public function findMany(object $payload, string $sortField, string $sortOrder)
    {
        $pageLength = min((int) ($payload->page_length ?? config('services.paginate')), 500);

        return User::filter($payload->all())
            ->orderBy($sortField, $sortOrder)
            ->paginate($pageLength);
    }

    public function findByUuid(string $uuid)
    {
        return User::where('uuid', $uuid)->firstOrFail();
    }

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

    public function update(object $payload, string $uuid)
    {
        $user = User::where('uuid', $uuid)->firstOrFail();
        $user->email = $payload->email ?? $user->email;
        $user->firstname = $payload->firstname ?? $user->firstname;
        $user->lastname = $payload->lastname ?? $user->lastname;
        $user->is_active = $payload->is_active ?? $user->is_active;

        if ($payload->password) {
            $user->password = Hash::make($payload->password);
        }

        $user->save();

        return $user->fresh();
    }

    public function delete(string $uuid)
    {
        $user = User::where('uuid', $uuid)->firstOrFail();
        $user->delete(); // rejected by the DB FK constraint if shifts/orders/payments still reference them
    }
}
