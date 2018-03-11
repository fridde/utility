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
        'y' => 31536000
    ];

    public static function toSeconds($value, string $unit = 's')
    {
        return (float) $value * self::$interval_factors[strtolower($unit)];
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
    public static function addDuration(array $duration, Carbon $time_to_be_changed = null)
    {
        $t = $time_to_be_changed ?? Carbon::now();
        $seconds = self::toSeconds(...$duration);

        return $t->addSeconds($seconds);
    }

    public static function subDuration(array $duration, Carbon $time_to_be_changed = null)
    {
        $duration[0] = -1.0 * $duration[0];

        return self::addDuration($duration, $time_to_be_changed);
    }


    public static function longerThanSince($duration, Carbon $since)
    {
        return Carbon::now()->gte(self::addDuration($duration, $since));
    }


}
