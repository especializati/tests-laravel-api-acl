<?php

use App\Http\Middleware\ACLMiddleware;
use App\Models\Permission;
use App\Models\User;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
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

test('should sync permissions of user', function () {
    assertDatabaseCount('permissions', 0);
    $arrayPermissions = Permission::factory()->count(10)->create()->pluck('id')->toArray();
    postJson(route('users.permissions.sync', $this->user->id), [
        'permissions' => $arrayPermissions
    ], [
        'Authorization' => 'Bearer ' . $this->token
    ])->assertOk();
    assertDatabaseCount('permissions', 10);
});

test('should validate permissions', function () {
    postJson(route('users.permissions.sync', $this->user->id), [
        'permissions' => ['fake_id_permission']
    ], [
        'Authorization' => 'Bearer ' . $this->token
    ])->assertStatus(422);
});
