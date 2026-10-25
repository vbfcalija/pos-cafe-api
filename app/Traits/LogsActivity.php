<?php

namespace App\Traits;

use App\Support\ActivityLogger;
use BackedEnum;
use DateTimeInterface;
use Illuminate\Support\Str;

/**
 * Records created / updated / deleted for a model in the activity log,
 * with the changed values (old → new) for updates.
 *
 * Only actions by a signed-in user are recorded: seeding, migrations and
 * scheduled jobs aren't "activity". A model can customise what's logged:
 *
 *   protected static array $activityEvents = ['updated'];    // which events
 *   protected array $activityIgnore = ['current_stocks'];     // fields to leave out
 *   public function activityLabel(): string                    // how the record is named
 *   public function activityDescription(string $event, array $changes): ?string
 *   public function activityEventName(string $event, array $changes): string
 */
trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        foreach (['created', 'updated', 'deleted'] as $event) {
            if (! in_array($event, static::activityEvents(), true)) {
                continue;
            }

            static::$event(function ($model) use ($event) {
                $model->recordActivity($event);
            });
        }
    }

    protected static function activityEvents(): array
    {
        return property_exists(static::class, 'activityEvents') ? static::$activityEvents : ['created', 'updated', 'deleted'];
    }

    public function recordActivity(string $event): void
    {
        if (ActivityLogger::isPaused() || ! ($user = ActivityLogger::currentUser())) {
            return;
        }

        $changes = $event === 'updated' ? $this->activityChanges() : [];

        if ($event === 'updated' && ! $changes) {
            return; // only ignored fields (timestamps etc.) changed
        }

        $properties = match ($event) {
            'updated' => ['changes' => $changes],
            'deleted' => ['attributes' => $this->activitySnapshot($this->getOriginal())],
            default => ['attributes' => $this->activitySnapshot($this->getAttributes())],
        };

        ActivityLogger::log(
            $this->activityEventName($event, $changes),
            $this->activityDescription($event, $changes) ?? $this->defaultActivityDescription($event),
            $this,
            $properties,
            $user,
        );
    }

    /**
     * How this record is named in the log, e.g. "Iced Latte (16oz)".
     */
    public function activityLabel(): string
    {
        foreach (['name', 'order_no', 'email'] as $attribute) {
            if (filled($this->getAttribute($attribute))) {
                return (string) $this->getAttribute($attribute);
            }
        }

        return (string) ($this->uuid ?? $this->getKey());
    }

    public function activityDescription(string $event, array $changes): ?string
    {
        return null;
    }

    public function activityEventName(string $event, array $changes): string
    {
        return $event;
    }

    public function activitySubjectName(): string
    {
        return Str::lower(Str::headline(class_basename(static::class)));
    }

    protected function defaultActivityDescription(string $event): string
    {
        return Str::ucfirst($event) . ' ' . $this->activitySubjectName() . ' “' . $this->activityLabel() . '”';
    }

    /**
     * Changed attributes as [field => ['old' => …, 'new' => …]], leaving out
     * timestamps and anything secret. A changed password is noted without
     * its value.
     */
    protected function activityChanges(): array
    {
        $changes = [];

        foreach ($this->getChanges() as $attribute => $new) {
            if (in_array($attribute, $this->activityIgnoredAttributes(), true)) {
                continue;
            }

            if ($this->isSecretAttribute($attribute)) {
                $changes[$attribute] = ['old' => null, 'new' => '(changed)'];

                continue;
            }

            $old = $this->normalizeActivityValue($this->getOriginal($attribute));
            $new = $this->normalizeActivityValue($this->getAttribute($attribute));

            if ($old !== $new) {
                $changes[$attribute] = ['old' => $old, 'new' => $new];
            }
        }

        return $changes;
    }

    protected function activitySnapshot(array $attributes): array
    {
        $snapshot = [];

        foreach (array_keys($attributes) as $attribute) {
            if (in_array($attribute, $this->activityIgnoredAttributes(), true) || $this->isSecretAttribute($attribute)) {
                continue;
            }

            $snapshot[$attribute] = $this->normalizeActivityValue($this->getAttribute($attribute));
        }

        return $snapshot;
    }

    protected function activityIgnoredAttributes(): array
    {
        return array_merge(
            ['id', 'uuid', 'created_at', 'updated_at'],
            property_exists($this, 'activityIgnore') ? $this->activityIgnore : [],
        );
    }

    protected function isSecretAttribute(string $attribute): bool
    {
        return in_array($attribute, ['password', 'remember_token'], true)
            || in_array($attribute, $this->getHidden(), true);
    }

    protected function normalizeActivityValue(mixed $value): mixed
    {
        return match (true) {
            $value instanceof BackedEnum => $value->value,
            $value instanceof DateTimeInterface => $value->format('Y-m-d H:i:s'),
            is_float($value) => round($value, 4),
            default => $value,
        };
    }
}
