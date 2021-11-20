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
}
