<?php

namespace App\Helpers;

class StarRatingHelper
{
    public static function generateStarRating(int $rating): string
    {
        $html = '';
        for ($i = 0; $i < $rating; $i++) {
            $html .= '<span style="color: #FFC107; font-size: 20px;">★</span>';
        }
        for ($i = $rating; $i < 5; $i++) {
            $html .= '<span style="color: #ccc; font-size: 20px;">★</span>';
        }
        return $html;
    }
}
