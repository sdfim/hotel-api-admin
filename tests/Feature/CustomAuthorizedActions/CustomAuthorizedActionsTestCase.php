<?php

namespace Tests\Feature\CustomAuthorizedActions;

use Tests\TestCase;

class CustomAuthorizedActionsTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->auth();
    }
}
