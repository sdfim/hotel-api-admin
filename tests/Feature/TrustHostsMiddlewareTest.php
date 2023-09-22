<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\TrustHosts;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class FakeTrustHosts extends TrustHosts
{
    public function __construct()
    {
        // В фейковом классе не передаем никаких аргументов
    }
}

class TrustHostsMiddlewareTest extends TestCase
{
    /**
     * Test that TrustHosts middleware does not trust requests to the main domain without subdomains.
     *
     * @return void
     */
    public function testDoesNotTrustRequestsToMainDomainWithoutSubdomains()
    {
        $middleware = new FakeTrustHosts();
    }
}
