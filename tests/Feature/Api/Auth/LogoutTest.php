<?php

use App\Models\User;

use function Pest\Laravel\postJson;

it('user authenticated should can logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test_e2e')->plainTextToken;
    postJson(route('auth.logout'), [], [
        'Authorization' => "Bearer {$token}"
    ])
        ->assertStatus(204);
});

it('user unauthenticated  cannot logout', function () {
    postJson(route('auth.logout'), [], [])
        ->assertJson([
            'message' => 'Unauthenticated.'
        ])
        ->assertStatus(401);
});
