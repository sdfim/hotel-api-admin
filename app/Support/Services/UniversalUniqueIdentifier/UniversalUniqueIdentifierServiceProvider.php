<?php

namespace App\Support\Services\UniversalUniqueIdentifier;

use Carbon\Laravel\ServiceProvider;

class UniversalUniqueIdentifierServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GeneratorContract::class, Generator::class);
    }
}
