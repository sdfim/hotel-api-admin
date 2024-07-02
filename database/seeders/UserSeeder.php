<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin_role = Role::where('slug', 'admin')->first();
        $createAdminPermission = Permission::where('slug', 'admin')->first();

        if (! User::where('email', 'admin@ujv.com')->first()) {
            $admin = new User();
            $admin->name = 'Admin';
            $admin->email = 'admin@ujv.com';
            $admin->email_verified_at = now();
            $admin->password = bcrypt('C5EV0gEU9OnlS5r');
            $admin->remember_token = 'XQpE1re2gyD2s8QkEwJKqYalM0M6IEPnNx22cDUKbMTzkoTvwVjANlLDTv39';
            $admin->created_at = now();
            $admin->updated_at = now();
            $admin->save();
            $admin->roles()->attach($admin_role);
            $admin->permissions()->attach($createAdminPermission);
        }

        $user_role = Role::where('slug', 'user')->first();
        $createUserPermission = Permission::where('slug', 'user')->first();

        if (! User::where('email', 'user@ujv.com')->first()) {
            $user = new User();
            $user->name = 'User';
            $user->email = 'user@ujv.com';
            $user->email_verified_at = now();
            $user->password = bcrypt('DStIXojk0DZqzNb');
            $user->remember_token = 'XPpE1re2gyD2s8QkEwJKqYalM0M6IEPnNx22cDUKbMTzkoTvwVjANlLDTv39';
            $user->created_at = now();
            $user->updated_at = now();
            $user->save();
            $user->roles()->attach($user_role);
            $user->permissions()->attach($createUserPermission);
        }
    }
}
