<?php

use App\Models\User;

use function Pest\Laravel\postJson;

test('should auth user', function () {
    $user = User::factory()->create();
    $data = [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'e2e_test',
    ];
    postJson(route('auth.login'), $data)
        ->assertOk()
        ->assertJsonStructure(['token']);
});
