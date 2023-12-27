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

test('should fail auth - with wrong password', function () {
    $user = User::factory()->create();
    $data = [
        'email' => $user->email,
        'password' => 'wrong-password',
        'device_name' => 'e2e_test',
    ];
    postJson(route('auth.login'), $data)->assertStatus(422);
});

test('should fail auth - with wrong email', function () {
    $user = User::factory()->create();
    $data = [
        'email' => 'fake@email.com',
        'password' => 'password',
        'device_name' => 'e2e_test',
    ];
    postJson(route('auth.login'), $data)->assertStatus(422);
});
