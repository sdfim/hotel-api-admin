<?php

test('admin geography index is opening', function () {
    $this->get('/admin/geography')
        ->assertStatus(200);
});
