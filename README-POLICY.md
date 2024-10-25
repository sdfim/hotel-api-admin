# Policy and Permissions

## Introduction
There are two types of permissions in the system: permissions corresponding to CRUD operations for models,
and permissions that only allow access for viewing. Simple permissions can be used to restrict access to
routes or controllers, while CRUD restrictions are implemented using policies.

## Creating a Policy

### Step 1: Creating the Policy

The policy should be created with the name of the model and the suffix `Policy`. For example, for the `User` model, 
the policy will be called `UserPolicy`. All policies must inherit from the `BasePolicy` class.

If the policy adheres to the naming conventions (i.e., named according to the `ModelPolicy` pattern), it will 
automatically be linked to the corresponding model. For all other models, you need to define the associations manually, 
especially if the models are not located in the `Models` folder.

### Step 2: Manual Association Specification

To manually specify the association between the model and the policy, you need to set the association in the 
static property `$modelPolicies` of the [PermissionServiceProvider.php](app/Providers/PermissionServiceProvider.php) 
class in the format: model — key, policy — value.

```php
private static array $modelPolicies = [
    User::class => UserPolicy::class,
];
```

### Step 3: Prefix for Permissions in the Policy

In the policy, you need to set a prefix for the permissions that will be used for the model. This prefix will automatically
be suffixed with `view`, `create`, `update`, or `delete`. If it is necessary to use the model for permission 
checks, you can also override the permission check method.

```php
<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy extends BasePolicy
{
    protected static string $prefix = 'user';

    public function update(User $user, User $model): bool
    {
        return $this->can('update', $user) && $user->id == $model->id;
    }
}
```

### Step 4: Adding Permissions to the Database

To add new permissions to the database, you need to include them in the [PermissionSeeder.php](database/seeders/PermissionSeeder.php). 
The prefixes specified in the policy are added to the `$prefixPermissions` array, while other permissions are added to the `$permissions` array.


```php
private static array $prefixPermissions = [
    'user',
];

private static array $permissions = [
    'other-permission',
];
```

## Adding Policy to the Controller

If you are using controllers for CRUD operations, access to the methods can be restricted using a policy. 
To do this, you need to inherit the controller from `BaseWithPolicyController` and specify the model 
it works with, for example, `User::class`.


```php
class UsersController extends BaseWithPolicyController
{
    protected static string $model = User::class;
}
```

This will automatically restrict access to the methods:

* `index` and `show` — checks permission `user.view`;
* `create` and `store` — checks permission `user.create`;
* `edit` and `update` — checks permission `user.update`;
* `destroy` — checks permission `user.delete`.

## Restricting Rights

### Throwing Exceptions When Permissions Are Lacking

To throw an exception when a permission is not granted, use the `Gate::authorize()` method.

```php
class StatisticChartsController extends Controller
{
    public function index(): View
    {
        Gate::authorize('statistic-charts');

        return view('dashboard.statistic-charts.index');
    }
}
```
```php
class PermissionsController extends Controller
{
    public function __invoke(): View
    {
        Gate::authorize('view', Permission::class);

        return view('dashboard.permissions.index');
    }
}
```

### Checking Without Throwing Exceptions

To check permissions without throwing exceptions, use the `Gate::allows()` method.

```php
if (Gate::allows('view', User::class)) {
    // code
}
```

### Using in Blade Templates

In Blade templates, you can check permissions using the `@can` directive.

```php
@can('view', User::class)
    <!-- content -->
@endcan
```


