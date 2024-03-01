<?php

use App\Models\Permission;
use App\Models\User;

use function Pest\Laravel\getJson;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->token = $this->user->createToken('test_e2e')->plainTextToken;
});

test('should return 403', function () {
    getJson(route('users.index'), [
        'Authorization' => 'Bearer ' . $this->token,
    ])->assertStatus(403);
});//->throws(CustomException::class);

test('should get resources users.index', function () {
    $permission = Permission::factory()->create(['name' => 'users.index']);
    $this->user->permissions()->attach($permission);
    getJson(route('users.index'), [
        'Authorization' => 'Bearer ' . $this->token,
    ])->assertStatus(200);
});

test('should get resources permissions.index', function () {
    $permission = Permission::factory()->create(['name' => 'permissions.index']);
    $this->user->permissions()->attach($permission);
    getJson(route('permissions.index'), [
        'Authorization' => 'Bearer ' . $this->token,
    ])->assertStatus(200);
});
