<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for products
        Permission::create(['name' => 'view products']);
        Permission::create(['name' => 'create products']);
        Permission::create(['name' => 'edit products']);
        Permission::create(['name' => 'delete products']);

        // Create permissions for orders
        Permission::create(['name' => 'view orders']);
        Permission::create(['name' => 'edit orders']);
        Permission::create(['name' => 'delete orders']);

        // Create permissions for users
        Permission::create(['name' => 'view users']);
        Permission::create(['name' => 'create users']);
        Permission::create(['name' => 'edit users']);
        Permission::create(['name' => 'delete users']);

        // Create permissions for categories
        Permission::create(['name' => 'view categories']);
        Permission::create(['name' => 'create categories']);
        Permission::create(['name' => 'edit categories']);
        Permission::create(['name' => 'delete categories']);

        // Create permissions for brands
        Permission::create(['name' => 'view brands']);
        Permission::create(['name' => 'create brands']);
        Permission::create(['name' => 'edit brands']);
        Permission::create(['name' => 'delete brands']);

        // Create permissions for coupons
        Permission::create(['name' => 'view coupons']);
        Permission::create(['name' => 'create coupons']);
        Permission::create(['name' => 'edit coupons']);
        Permission::create(['name' => 'delete coupons']);

        // Create permissions for reviews
        Permission::create(['name' => 'view reviews']);
        Permission::create(['name' => 'approve reviews']);
        Permission::create(['name' => 'delete reviews']);

        // Create permissions for reports
        Permission::create(['name' => 'view reports']);
        Permission::create(['name' => 'export reports']);

        // Create Customer role with no admin permissions
        $customerRole = Role::create(['name' => 'customer']);
        $customerRole->givePermissionTo([
            'view products',
            'view categories',
            'view brands',
        ]);

        // Create Admin role with all permissions
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $this->command->info('✓ Roles and permissions created successfully!');
        $this->command->info('✓ Created roles: customer, admin');
        $this->command->info('✓ Created ' . Permission::count() . ' permissions');
    }
}
