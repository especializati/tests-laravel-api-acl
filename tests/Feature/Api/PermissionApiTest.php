<?php

use Illuminate\Support\Str;
use App\Http\Middleware\ACLMiddleware;
use App\Models\Permission;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\assertSoftDeleted;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;
use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    withoutMiddleware([ACLMiddleware::class]);
    $this->permission = Permission::factory()->create();
    $this->user = User::factory()->create();
    $this->token = $this->user->createToken('test_e2e')->plainTextToken;
});

test('should return 200', function () {
    getJson(
        route('permissions.index'),
        [
            'Authorization' => 'Bearer ' . $this->token
        ]
    )->assertJsonStructure([
        'data' => [
            '*' => [
                'id', 'name', 'description'
            ]
        ]
    ])->assertOk();
});

test('should return permissions page 2', function () {
    Permission::factory()->count(22)->create();
    $response = getJson(
        route('permissions.index') . '?page=2',
        [
            'Authorization' => 'Bearer ' . $this->token
        ]
    )->assertJsonStructure([
        'data' => [
            '*' => ['id', 'name', 'description']
        ],
        'meta' => ['total', 'current_page', 'from', 'last_page', 'links' => [], 'path', 'per_page', 'to']
    ])->assertOk();

    expect(count($response['data']))->toBe(8);
    expect($response['meta']['total'])->toBe(23);
});

test('should return permissions with total_per_page', function () {
    Permission::factory()->count(16)->create();
    $response = getJson(
        route('permissions.index') . '?total_per_page=4',
        [
            'Authorization' => 'Bearer ' . $this->token
        ]
    )->assertJsonStructure([
        'data' => [
            '*' => ['id', 'name', 'description']
        ],
        'meta' => ['total', 'current_page', 'from', 'last_page', 'links' => [], 'path', 'per_page', 'to']
    ])->assertOk();

    expect(count($response['data']))->toBe(4);
    expect($response['meta']['total'])->toBe(17);
    expect($response['meta']['per_page'])->toBe(4);
});

test('should return permissions with filter', function () {
    Permission::factory()->count(10)->create();
    Permission::factory()->create(['name' => 'custom_permission_name']);
    $response = getJson(
        route('permissions.index') . '?filter=custom_permission_name',
        [
            'Authorization' => 'Bearer ' . $this->token
        ]
    )->assertJsonStructure([
        'data' => [
            '*' => ['id', 'name', 'description']
        ],
        'meta' => ['total', 'current_page', 'from', 'last_page', 'links' => [], 'path', 'per_page', 'to']
    ])->assertOk();

    expect(count($response['data']))->toBe(1);
    expect($response['meta']['total'])->toBe(1);
});

test('should create new permission', function () {
    $response = postJson(route('permissions.store'), [
        'name' => 'users.index',
        'description' => 'can list users'
    ], [
        'Authorization' => 'Bearer ' . $this->token
    ])->assertCreated();

    assertDatabaseHas('permissions', [
        'id' => $response['data']['id'],
        'name' => 'users.index',
        'description' => 'can list users'
    ]);
});

describe('validations', function () {
    test('should validate create new permission', function () {
        postJson(route('permissions.store'), [], [
            'Authorization' => 'Bearer ' . $this->token
        ])->assertStatus(422)->assertJsonValidationErrors([
            'name' => trans('validation.required', ['attribute' => 'name']),
            'description' => trans('validation.required', ['attribute' => 'description']),
        ]);
    });
    test('should validate update permission', function () {
        putJson(route('permissions.update', $this->permission->id), [], [
            'Authorization' => 'Bearer ' . $this->token
        ])->assertStatus(422)->assertJsonValidationErrors([
            'name' => trans('validation.required', ['attribute' => 'name']),
            'description' => trans('validation.required', ['attribute' => 'description'])
        ]);
    });
    test('should validate update permission - with name less 3 characters', function () {
        putJson(route('permissions.update', $this->permission->id), [
            'name' => 'ab',
            'description' => 'List users',
        ], [
            'Authorization' => 'Bearer ' . $this->token
        ])->assertStatus(422)->assertJsonValidationErrors([
            'name' => trans('validation.min.string', ['attribute' => 'name', 'min' => 3])
        ]);
    });
    test('should validate update permission - The name field must not be greater than 255 characters', function () {
        putJson(route('permissions.update', $this->permission->id), [
            'name' => Str::random(500),
            'description' => 'list all users',
        ], [
            'Authorization' => 'Bearer ' . $this->token
        ])->assertStatus(422)->assertJsonValidationErrors([
            'name' => trans('validation.max.string', ['attribute' => 'name', 'max' => 255])
        ]);
    });
});

test('should return permission', function () {
    getJson(route('permissions.show', $this->permission->id), [
        'Authorization' => 'Bearer ' . $this->token
    ])->assertOk()
        ->assertJsonStructure([
            'data' => ['id', 'name', 'description']
        ]);
});

test('should return 404 when permission not found', function () {
    getJson(route('permissions.show', 'fake_id'), [
        'Authorization' => 'Bearer ' . $this->token
    ])->assertNotFound();
});

test('should update permission', function () {
    putJson(route('permissions.update', $this->permission->id), [
        'name' => 'users.store',
        'description' => 'can create new user',
    ], [
        'Authorization' => 'Bearer ' . $this->token
    ])->assertOk();

    assertDatabaseHas('permissions', [
        'id' => $this->permission->id,
        'name' => 'users.store',
        'description' => 'can create new user',
    ]);
});

test('should return 404 when not exists permission', function () {
    putJson(route('permissions.update', 'fake_id'), [
        'name' => 'users.store',
        'description' => 'can create new user',
    ], [
        'Authorization' => 'Bearer ' . $this->token
    ])->assertNotFound();
});

test('should delete permission', function () {
    deleteJson(route('permissions.destroy', $this->permission->id), [], [
        'Authorization' => 'Bearer ' . $this->token
    ])->assertNoContent();

    assertDatabaseMissing('permissions', [
        'id' => $this->permission->id
    ]);
});

test('should return 404 when not exists permission - delete', function () {
    deleteJson(route('permissions.destroy', 'fake_id'), [], [
        'Authorization' => 'Bearer ' . $this->token
    ])->assertNotFound();
});
