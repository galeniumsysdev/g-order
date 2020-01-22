<?php

use Illuminate\Database\Seeder;
use App\Permission;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
          [
            'name'=>'role',
            'display_name'=>'Manage Role',
            'description'=>'Create, Update, Delete Role'
          ],
          [
            'name'=>'product',
            'display_name'=>'Manage Product',
            'description'=>'Create,Update Product'
          ],
          [
            'name'=>'user',
            'display_name'=>'Manage User',
            'description'=>'Create, Update, Inactive User'
          ],
          [
            'name'=>'Outlet/Distributor',
            'display_name'=>'Manage Customer',
            'description'=>'Create, Update Outlet/Distributor'
          ],
          [
            'name'=>'Categories Outlet',
            'display_name'=>'Categories Outlet',
            'description'=>'Create, Update Category Outlet'
          ],
        ];
        foreach ($permissions as $key=>$value){
          Permission::create($value);
        }
    }
}
