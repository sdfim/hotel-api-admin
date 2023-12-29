<?php

namespace Tests\Feature\CustomAuthorizedActions;

use Tests\TestCase;

class CustomAuthorizedActionsTestCase extends TestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->auth();
    }
}
