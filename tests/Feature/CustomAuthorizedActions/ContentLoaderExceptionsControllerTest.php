<?php

test('content loader exceptions is opening', function () {
    $this->get('/admin/exceptions-report')
        ->assertStatus(200);
});
