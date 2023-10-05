<?php

namespace Database\Seeders;

use App\Enums\PermissionCategory;
use App\Enums\PermissionsEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate first
        Permission::query()->truncate();

        foreach (PermissionsEnum::cases() as $permission) {
            Permission::create(['name' => $permission->value, 'category_id' => $permission->category()]);
        }

        // Enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
