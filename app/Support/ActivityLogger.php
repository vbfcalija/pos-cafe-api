<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Throwable;

/**
 * Writes activity log rows, filling in who and where from the current
 * request. Logging never breaks the action being logged: a failure here is
 * reported and swallowed.
 */
class ActivityLogger
{
    private static bool $paused = false;

    /**
     * Runs $callback without recording model changes — for bookkeeping side
     * effects (e.g. stock going down with each sale) that would only bury
     * the real actions.
     */
    public static function withoutLogging(callable $callback): mixed
    {
        $previous = self::$paused;
        self::$paused = true;

        try {
            return $callback();
        } finally {
            self::$paused = $previous;
        }
    }

    public static function isPaused(): bool
    {
        return self::$paused;
    }

    /**
     * The signed-in user making this request, or null (console, seeding,
     * the nightly checklist job, unauthenticated requests).
     */
    public static function currentUser(): ?User
    {
        try {
            $user = app()->bound('request') ? request()->user() : null;
        } catch (Throwable) {
            return null;
        }

        return $user instanceof User ? $user : null;
    }

    public static function log(
        string $event,
        string $description,
        ?Model $subject = null,
        array $properties = [],
        ?User $user = null,
        array $context = [],
    ): ?ActivityLog {
        try {
            return ActivityLog::create(array_merge([
                'user_id' => ($user ?? self::currentUser())?->id,
                'event' => $event,
                'description' => mb_substr($description, 0, 255),
                'subject_type' => $subject ? $subject->getMorphClass() : null,
                'subject_id' => $subject?->getKey(),
                'properties' => $properties ?: null,
            ], self::requestContext(), array_filter($context, fn ($value) => filled($value))));
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }

    /**
     * IP, device ID and parsed User-Agent for the current request. The app
     * sends its device ID on every request as X-Device-Id.
     */
    public static function requestContext(): array
    {
        try {
            $request = app()->bound('request') ? request() : null;
        } catch (Throwable) {
            $request = null;
        }

        if (! $request) {
            return [];
        }

        $userAgent = $request->userAgent();
        $deviceId = $request->header('X-Device-Id');

        return array_merge(UserAgent::parse($userAgent), [
            'ip_address' => $request->ip(),
            'device_id' => $deviceId ? mb_substr((string) $deviceId, 0, 64) : null,
            'user_agent' => $userAgent ? mb_substr($userAgent, 0, 1000) : null,
        ]);
    }
}
