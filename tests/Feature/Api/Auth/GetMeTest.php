<?php

use App\Models\Permission;
use App\Models\User;

use function Pest\Laravel\getJson;

test('unauthenticated user cannot get our data', function () {
    getJson(route('auth.me'), [])
        ->assertJson([
            'message' => 'Unauthenticated.'
        ])
        ->assertStatus(401);
});

test('should return user with our data', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test_e2e')->plainTextToken;
    getJson(route('auth.me'), [
        'Authorization' => "Bearer {$token}"
    ])
    ->assertJsonStructure([
        'data' => [
            'id',
            'name',
            'email',
            'permissions' => []
        ]
    ])
    ->assertOk();
});

test('should return user with our data and our permissions', function () {
    Permission::factory()->count(10)->create();
    $permissionsIds = Permission::factory()->count(10)->create()->pluck('id')->toArray();
    $user = User::factory()->create();
    $token = $user->createToken('test_e2e')->plainTextToken;
    $user->permissions()->attach($permissionsIds);
    getJson(route('auth.me'), [
        'Authorization' => "Bearer {$token}"
    ])
    ->assertJsonStructure([
        'data' => [
            'id',
            'name',
            'email',
            'permissions' => [
                '*' => [
                    'id',
                    'name',
                    'description'
                ]
            ]
        ]
    ])
    ->assertJsonCount(10, 'data.permissions')
    ->assertOk();
});
