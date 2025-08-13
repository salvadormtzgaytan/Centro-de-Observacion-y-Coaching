<?php

use Illuminate\Support\Facades\DB;

test('returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('checa que corremos en mysql', function () {
    expect(DB::connection()->getDriverName())->toBe('mysql');
});
