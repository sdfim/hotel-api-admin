<?php
namespace App\Http;

use Illuminate\Routing\UrlGenerator;

class CustomUrlGenerator extends UrlGenerator
{
    public function route($name, $parameters = [], $absolute = true)
    {
        return parent::route($name, $parameters, false);
    }
}