<?php

namespace Modules\Utils;

use ResourceBundle;

class Tools
{
    public static function getCurrencyOptions(): array
    {
        $options = [];
        $currencies = ResourceBundle::create('en', 'ICUDATA-curr')->get('Currencies');

        foreach ($currencies as $code => $details) {
            $options[$code] = $code;
        }

        return $options;
    }

    public static function getLanguageOptions(): array
    {
        $options = [];
        $languages = ResourceBundle::create('en', 'ICUDATA-languages')->get('Languages');

        foreach ($languages as $code => $details) {
            $options[$code] = $details['name'] ?? $code;
        }

        return $options;
    }
}
