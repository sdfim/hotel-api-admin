<?php

namespace App\Support\Services\UniversalUniqueIdentifier;

interface GeneratorContract
{
    public function getUuidRequestKey(): string;

    public function uuidv4(?string $salt = null, bool $forceNew = false): string;
}
