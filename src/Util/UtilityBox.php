<?php

namespace App\Util;

/**
 * Custom utility methods.
 */
class UtilityBox
{
    /**
     * Add ! prefix to banned words for searching.
     * @param array $words Default requests' options
     *
     * @return array
     */
    public static function addExclPrefix(array $words = [])
    {
        foreach ($words as &$value) {
            $value = '!'.$value;
        }
        unset($value);

        return $words;
    }

    /**
     * Dynamically generates a max price to set it as the max price parameter for the remote search query.
     *
     * @param int $lastAveragePrice The last average price found.
     *
     * @return int
     */
    public static function generateMaxPrice($lastAveragePrice)
    {
        // Sum the third of the last average price value.
        $thirdLastAveragePrice = $lastAveragePrice / 3;
        $maxPrice = $lastAveragePrice + $thirdLastAveragePrice;

        // Round up max price to the nearest 10 and return value.
        return ceil($maxPrice / 10) * 10;
    }
}
