<?php

namespace Fridde;

use Carbon\Carbon;

class Timing
{
    private static $interval_factors = [
        'ms' => 0.001,
        's' => 1,
        'm' => 60,
        'h' => 3600,
        'd' => 86400,
        'w' => 604800,
        'y' => 31536000,
    ];

    public static function toSeconds($value, string $unit = 's')
    {
        return (float)$value * self::$interval_factors[strtolower($unit)];
    }

    /**
     * Adds a duration to a DateTime
     *
     * @param array $duration Duration of the type [value, "unit"]
     * @param null|Carbon $time_to_be_changed The point in time.
     *                                               If omitted, the current time is assumed.
     *
     * @return Carbon The new time after addition.
     */
    public static function addDuration(array $duration, Carbon &$time_to_be_changed)
    {
        $seconds = self::toSeconds(...$duration);

        return $time_to_be_changed->addSeconds($seconds);
    }

    public static function subDuration(array $duration, Carbon &$time_to_be_changed)
    {
        $duration = self::multiplyDurationBy($duration, -1.0);

        return self::addDuration($duration, $time_to_be_changed);
    }

    public static function addDurationToNow(array $duration): Carbon
    {
        $now = Carbon::now()->copy();

        return self::addDuration($duration, $now);
    }

    public static function subDurationFromNow(array $duration): Carbon
    {
        $now = Carbon::now()->copy();

        return self::subDuration($duration, $now);
    }


    public static function longerThanSince(array $duration, Carbon $since): bool
    {
        return Carbon::now()->gte(self::addDuration($duration, $since));
    }

    public static function multiplyDurationBy(array $duration, float $factor): array
    {
        return [$factor * $duration[0], $duration[1]];
    }


}
