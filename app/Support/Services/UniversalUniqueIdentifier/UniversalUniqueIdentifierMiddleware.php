<?php

namespace App\Support\Services\UniversalUniqueIdentifier;

use Closure;
use Illuminate\Http\Request;

class UniversalUniqueIdentifierMiddleware
{
    public const UUID_KEY = 'uuid';

    public function __construct(protected GeneratorContract $generator)
    {
    }

    public function handle(Request $request, Closure $next): mixed
    {

        $request->attributes->add([static::UUID_KEY => $request->header('x-admin-session-uuid', $this->generator->uuidv4())]);
        return $next($request);
    }
}
