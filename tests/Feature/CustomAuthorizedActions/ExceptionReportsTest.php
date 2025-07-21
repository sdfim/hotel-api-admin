<?php

test('exception reports index is opening', function () {
    $this->get('/admin/exceptions-report')
        ->assertStatus(200);
});
