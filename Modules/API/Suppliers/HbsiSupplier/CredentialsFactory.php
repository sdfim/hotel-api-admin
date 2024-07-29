<?php

namespace Modules\API\Suppliers\HbsiSupplier;

class CredentialsFactory
{
    public static function fromConfig(): Credentials
    {
        $namespace = 'booking-suppliers.HBSI.credentials';
        $credentials = new Credentials();

        $credentials->username = config("$namespace.username");
        $credentials->password = config("$namespace.password");
        $credentials->channelIdentifierId = config("$namespace.channel_identifier_id");
        $credentials->searchBookUrl = config("$namespace.search_book_url");
        $credentials->target = config("$namespace.target");
        $credentials->componentInfoId = config("$namespace.component_info_id");

        //TODO: Validate outside constructor to avoid build errors.
        //        if (!$credentials->username || !$credentials->password || !$credentials->channelIdentifierId)
        //        {
        //            throw new \Exception("Not all HBSI Credentials are set, please check your .env file");
        //        }

        return $credentials;
    }
}
