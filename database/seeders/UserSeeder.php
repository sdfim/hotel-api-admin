<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Team;
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

        $admin = User::withTrashed()->updateOrCreate(
            ['email' => 'admin@ujv.com'],
            [
                'name' => 'Admin',
                'email_verified_at' => now(),
                'password' => bcrypt('C5EV0gEU9OnlS5r'),
                'remember_token' => 'XQpE1re2gyD2s8QkEwJKqYalM0M6IEPnNx22cDUKbMTzkoTvwVjANlLDTv39',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        $admin->roles()->sync([$admin_role->id]);
        $admin->permissions()->sync([$createAdminPermission->id]);

        $adminTeam = Team::firstOrCreate(
            ['user_id' => $admin->id, 'personal_team' => true],
            ['name' => $admin->name."'s Team"]
        );
        $admin->ownedTeams()->save($adminTeam);
        $admin->switchTeam($adminTeam);

        $user_role = Role::where('slug', 'user')->first();
        $createUserPermission = Permission::where('slug', 'user')->first();

        $user = User::withTrashed()->updateOrCreate(
            ['email' => 'user@ujv.com'],
            [
                'name' => 'User',
                'email_verified_at' => now(),
                'password' => bcrypt('DStIXojk0DZqzNb'),
                'remember_token' => 'XPpE1re2gyD2s8QkEwJKqYalM0M6IEPnNx22cDUKbMTzkoTvwVjANlLDTv39',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        $user->roles()->sync([$user_role->id]);
        $user->permissions()->sync([$createUserPermission->id]);
    }
}
