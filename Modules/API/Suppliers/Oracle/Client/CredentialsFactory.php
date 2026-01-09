<?php

namespace Modules\API\Suppliers\Oracle\Client;

class CredentialsFactory
{
    public static function fromConfig(): Credentials
    {
        $namespace = 'booking-suppliers.Oracle.credentials';
        $credentials = new Credentials();

        $credentials->username = config("$namespace.username");
        $credentials->password = config("$namespace.password");
        $credentials->basicUsername = config("$namespace.basic_username");
        $credentials->basicPassword = config("$namespace.basic_password");
        $credentials->appKey = config("$namespace.app_key");
        $credentials->baseUrl = config("$namespace.base_url");

        return $credentials;
    }
}

