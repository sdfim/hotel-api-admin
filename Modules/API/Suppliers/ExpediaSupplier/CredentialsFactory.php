<?php

namespace Modules\API\Suppliers\ExpediaSupplier;

class CredentialsFactory
{
    public static function fromConfig(): Credentials
    {
        $namespace = 'booking-suppliers.Expedia.credentials';
        $credentials = new Credentials();

        $credentials->apiKey = config("$namespace.api_key");
        $credentials->sharedSecret = config("$namespace.shared_secret");
        $credentials->rapidBaseUrl = config("$namespace.rapid_base_url");
        //TODO: Validate outside constructor to avoid build errors.
        //        if (!$credentials->apiKey || !$credentials->sharedSecret || !$credentials->rapidBaseUrl)
        //        {
        //            throw new \Exception("Not all Expedia Credentials are set, please check your .env file");
        //        }

        return $credentials;
    }
}
