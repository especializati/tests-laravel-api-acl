<?php

use App\Http\Middleware\ACLMiddleware;
use App\Models\User;

use function Pest\Laravel\getJson;
use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    withoutMiddleware([ACLMiddleware::class]);
    $this->user = User::factory()->create();
    $this->token = $this->user->createToken('test_e2e')->plainTextToken;
});

test('should return 200', function () {
    getJson(
        route('users.index'),
        [
            'Authorization' => 'Bearer ' . $this->token
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

test('should return 200 - with many users', function () {
    User::factory()->count(20)->create();
    $response = getJson(
        route('users.index'),
        [
            'Authorization' => 'Bearer ' . $this->token
        ]
    )->assertJsonStructure([
        'data' => [
            '*' => [
                'id', 'name', 'email',
                'permissions' => []
            ]
        ],
        'meta' => ['total', 'current_page', 'from', 'last_page', 'links' => [], 'path', 'per_page', 'to']
    ])->assertOk();

    expect(count($response['data']))->toBe(15);
    expect($response['meta']['total'])->toBe(21);
});

test('should return users page 2', function () {
    User::factory()->count(22)->create();
    $response = getJson(
        route('users.index') . '?page=2',
        [
            'Authorization' => 'Bearer ' . $this->token
        ]
    )->assertJsonStructure([
        'data' => [
            '*' => ['id', 'name', 'email','permissions' => []]
        ],
        'meta' => ['total', 'current_page', 'from', 'last_page', 'links' => [], 'path', 'per_page', 'to']
    ])->assertOk();

    expect(count($response['data']))->toBe(8);
    expect($response['meta']['total'])->toBe(23);
});
