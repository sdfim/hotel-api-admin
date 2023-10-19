<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $manager = new Role();
        $manager->name = 'Admin';
        $manager->slug = 'admin';
        $manager->save();

        $developer = new Role();
        $developer->name = 'User';
        $developer->slug = 'user';
        $developer->save();
    }
}
