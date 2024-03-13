# Adicionar Permissões ao Usuário

```sh
docker compose up -d
docker compose exec app bash
php artisan tinker
$permissionUserList = \App\Models\Permission::factory()->create(['name' => 'users.index']);
$permissionUserStore = \App\Models\Permission::factory()->create(['name' => 'users.store']);
$permissionUserUpdate = \App\Models\Permission::factory()->create(['name' => 'users.update']);
$permissionUserDestroy = \App\Models\Permission::factory()->create(['name' => 'users.destroy']);

$user = User::where('email', 'carlos@especializati.com.br')->first();
$user->permissions()->attach([
    $permissionUserList->id,
    $permissionUserStore->id,
    $permissionUserUpdate->id,
    $permissionUserDestroy->id
]);
$user->permissions()->get()->pluck('name');
```
```sh
$permissionsList = \App\Models\Permission::factory()->create(['name' => 'permissions.index']);
$permissionsStore = \A-pp\Models\Permission::factory()->create(['name' => 'permissions.store']);
$permissionsUpdate = \App\Models\Permission::factory()->create(['name' => 'permissions.update']);
$permissionsDestroy =+ \App\Models\Permission::factory()->create(['name' => 'permissions.destroy']);

$user = User::where('email', 'carlos@especializati.com.br')->first();
$user->permissions()->attach([
    $permissionsList->id,
    $permissionsStore->id,
    $permissionsUpdate->id,
    $permissionsDestroy->id
]);
$user->permissions()->get()->pluck('name');
```
