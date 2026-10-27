<?php

namespace App\Support;

class MarkUi
{
    /** Colour class for a grade. */
    public static function tone(?string $grade): string
    {
        return match (true) {
            in_array($grade, ['A+', 'A', 'A-']) => 't-a',
            in_array($grade, ['B+', 'B', 'B-']) => 't-b',
            in_array($grade, ['C+', 'C', 'D'])  => 't-c',
            $grade === 'F'                      => 't-f',
            default                             => 't-i',
        };
    }

    /** 12.50 => 12.5, 10.00 => 10 */
    public static function fmt($n): string
    {
        return rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
    }
}