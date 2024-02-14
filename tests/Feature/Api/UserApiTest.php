<?php

use App\Http\Middleware\ACLMiddleware;
use App\Models\User;

use function Pest\Laravel\getJson;
use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    withoutMiddleware([ACLMiddleware::class]);
});

test('should return 200', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test_e2e')->plainTextToken;
    getJson(
        route('users.index'),
        [
            'Authorization' => 'Bearer ' . $token
        ]
    )->assertJsonStructure([
        'data' => [
            '*' => [
                'id', 'name', 'email',
                'permissions' => []
            ]
        ]
    ])->assertOk();
});
