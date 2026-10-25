<?php

namespace App\Service;

use App\Http\Resources\UserResource;
use App\Interface\Repository\UserRepositoryInterface;
use App\Interface\Service\AuthServiceInterface;
use App\Models\User;
use App\Support\ActivityLogger;
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
            $this->logLogin($payload, 'login_failed', $user, $user ? 'Wrong password' : 'Unknown email');

            return response()->json([
                'message' => 'These credentials do not match our records.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (! $user->is_active) {
            $this->logLogin($payload, 'login_failed', $user, 'Account deactivated');

            return response()->json([
                'message' => 'This account has been deactivated.',
            ], Response::HTTP_FORBIDDEN);
        }

        $token = $user->createToken('pos-cafe-api');
        $this->logLogin($payload, 'login', $user, null, $token->accessToken->id);

        $data = (object) [
            'token' => $token->plainTextToken,
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
        $user = $payload->user();
        ActivityLogger::log('logout', "{$user->activityLabel()} signed out", $user, [
            'token_id' => $user->currentAccessToken()?->id,
        ], $user);

        $user->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ], Response::HTTP_OK);
    }

    /**
     * Records a sign-in attempt with everything known about where it came
     * from: IP (plus any proxy chain), the app's device ID, the parsed
     * User-Agent, and what the app reported about the device.
     */
    private function logLogin(object $payload, string $event, ?User $user, ?string $failureReason = null, ?int $tokenId = null): void
    {
        $device = (array) ($payload->device ?? []);
        $name = $user?->activityLabel() ?? $payload->email;

        ActivityLogger::log(
            $event,
            $event === 'login' ? "{$name} signed in" : "Failed sign-in for {$payload->email}: {$failureReason}",
            $user,
            array_filter([
                'email' => $payload->email,
                'failure_reason' => $failureReason,
                'token_id' => $tokenId,
                'forwarded_for' => $payload->header('X-Forwarded-For'),
                'device' => array_filter($device, fn ($value) => $value !== null && $value !== ''),
            ], fn ($value) => $value !== null && $value !== []),
            $user,
            [
                // Prefer what the app reports; the User-Agent fills in the rest.
                'device_id' => $device['id'] ?? null,
                'device_model' => $device['model'] ?? null,
                'device_type' => isset($device['mobile']) && $device['mobile'] ? 'mobile' : null,
            ],
        );
    }
}
