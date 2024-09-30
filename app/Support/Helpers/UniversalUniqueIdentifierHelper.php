<?php

namespace App\Support\Helpers;


use App\Support\Services\UniversalUniqueIdentifier\UniversalUniqueIdentifierMiddleware;
use Exception;

class UniversalUniqueIdentifierHelper
{
    /**
     * @var string|null
     */
    protected static ?string $uuidv4 = null;

    public static function getUuidRequestKey(): string
    {
        return UniversalUniqueIdentifierMiddleware::UUID_KEY;
    }

    /**
     * @throws Exception
     */
    public static function uuidv4(?string $salt = null, bool $forceNew = false): string
    {
        if ($forceNew || is_null(static::$uuidv4)) {
            static::$uuidv4 = strtolower(static::newUuidv4($salt));
        }

        return static::$uuidv4;
    }

    /**
     * @throws Exception
     */
    protected static function newUuidv4(string $data = null): string
    {
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = $data ?? random_bytes(16);
        assert(strlen($data) === 16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

