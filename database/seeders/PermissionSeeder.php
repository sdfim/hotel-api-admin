<?php
namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $manageUser = new Permission();
        $manageUser->name = 'Admin';
        $manageUser->slug = 'admin';
        $manageUser->save();

        $createTasks = new Permission();
        $createTasks->name = 'User';
        $createTasks->slug = 'user';
        $createTasks->save();
    }
}