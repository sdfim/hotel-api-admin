<?php

namespace App\Support\Services\UniversalUniqueIdentifier;

use Exception;

class Generator implements GeneratorContract
{
    /**
     * @var string|null
     */
    private ?string $uuidv4 = null;

    public function getUuidRequestKey(): string
    {
        return UniversalUniqueIdentifierMiddleware::UUID_KEY;
    }

    /**
     * @throws Exception
     */
    public function uuidv4(?string $salt = null, bool $forceNew = false): string
    {
        if ($forceNew || is_null($this->uuidv4)) {
            $this->uuidv4 = strtolower($this->newUuidv4($salt));
        }

        return $this->uuidv4;
    }

    /**
     * @throws Exception
     */
    protected function newUuidv4(string $data = null): string
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
