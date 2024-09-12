<?php

namespace App\Livewire\Helpers;

class ViewHelpers
{
    public static function compressString(string $str, int $startLength = 4, int $endLength = 6, string $separator = '...'): string
    {
        return substr($str, 0, $startLength) . $separator . substr($str, -$endLength);
    }
}
