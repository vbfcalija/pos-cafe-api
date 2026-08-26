<?php

namespace App\Service;

use App\Http\Resources\UserResource;
use App\Interface\Repository\UserRepositoryInterface;
use App\Interface\Service\AuthServiceInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService implements AuthServiceInterface
{
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function login(object $payload)
    {
        $user = $this->userRepository->findByEmail($payload->email);

        // Same message for "no such user" and "wrong password" so a login
        // attempt can't be used to enumerate registered emails.
        if (! $user || ! Hash::check($payload->password, $user->password)) {
            return response()->json([
                'message' => 'These credentials do not match our records.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (! $user->is_active) {
            return response()->json([
                'message' => 'This account has been deactivated.',
            ], Response::HTTP_FORBIDDEN);
        }

        $data = (object) [
            'token' => $user->createToken('pos-cafe-api')->plainTextToken,
            'user' => new UserResource($user),
        ];

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    public function register(object $payload)
    {
        // Both writes (the user row, the token row) succeed together or not
        // at all — without this, a token-creation failure would leave a
        // committed user row behind for a request the client saw fail,
        // which then silently succeeds if they retry register with the
        // same email (a confusing "duplicate email" error on a request
        // they believe never went through).
        $data = DB::transaction(function () use ($payload) {
            $user = $this->userRepository->create($payload);

            return (object) [
                'token' => $user->createToken('pos-cafe-api')->plainTextToken,
                'user' => new UserResource($user),
            ];
        });

        return response()->json(['data' => $data], Response::HTTP_CREATED);
    }

    public function logout(object $payload)
    {
        $payload->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ], Response::HTTP_OK);
    }
}
