<?php

use App\Http\Middleware\ACLMiddleware;
use App\Models\Permission;
use App\Models\User;

use function Pest\Laravel\getJson;
use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    withoutMiddleware([ACLMiddleware::class]);
    $this->user = User::factory()->create();
    $this->token = $this->user->createToken('test_e2e')->plainTextToken;
});

test('should return all permissions of user - with empty permissions', function () {
    getJson(route('users.permissions', $this->user->id), [
        'Authorization' => 'Bearer ' . $this->token
    ])->assertOk()->assertJsonStructure([
        'data' => [
            '*' => ['id', 'name', 'description']
        ]
    ]);
});

test('should return all permissions of user - with permissions', function () {
    Permission::factory()->count(10)->create();
    $permissions = Permission::factory()->count(10)->create();
    $this->user->permissions()->sync($permissions->pluck('id')->toArray());
    getJson(route('users.permissions', $this->user->id), [
        'Authorization' => 'Bearer ' . $this->token
    ])->assertOk()->assertJsonStructure([
        'data' => [
            '*' => ['id', 'name', 'description']
        ]
    ])->assertJsonCount(10, 'data');
});
