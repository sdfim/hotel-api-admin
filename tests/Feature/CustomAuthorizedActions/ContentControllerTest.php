<?php

test('admin index is opening', function () {
    $this->get('/admin/content')
        ->assertStatus(200);
});
