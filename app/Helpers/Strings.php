<?php

namespace App\Helpers;

class Strings
{
    public static function prepareSearchForBooleanMode(string $search): string
    {
        $words = explode(' ', $search);
    
        // Prepend + to each word, ensuring that each word is mandatory
        // Append * to each word, ensuring that pieces of words can be matched
        $booleanSearch = collect($words)->map(function ($word) {
            return "+$word*";
        })->implode(' ');
    
        return $booleanSearch;
    }
}
