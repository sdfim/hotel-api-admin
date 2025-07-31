<?php

namespace Modules\API\Suppliers\HotelTraderSupplier;

class CredentialsFactory
{
    public static function fromConfig(): Credentials
    {
        $namespace = 'booking-suppliers.HotelTrader.credentials';
        $credentials = app(Credentials::class);

        $credentials->username = config("$namespace.username");
        $credentials->password = config("$namespace.password");

        // New GraphQL-specific URLs
        $credentials->graphqlSearchUrl = config("$namespace.graphql_search_url");
        $credentials->graphqlBookUrl = config("$namespace.graphql_book_url");

        return $credentials;
    }
}
