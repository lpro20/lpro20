<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */

        $permissions = [
            // Produits
            'products.view',
            'products.create',
            'products.update',
            'products.delete',

            // Catégories
            'categories.view',
            'categories.create',
            'categories.update',
            'categories.delete',

            // Marques
            'brands.view',
            'brands.create',
            'brands.update',
            'brands.delete',

            // Tailles
            'sizes.view',
            'sizes.create',
            'sizes.update',
            'sizes.delete',

            // Couleurs
            'colors.view',
            'colors.create',
            'colors.update',
            'colors.delete',

            // Stock
            'stock.view',
            'stock.adjust',
            'stock.manage',

            // Achats
            'purchases.view',
            'purchases.create',
            'purchases.update',
            'purchases.delete',

            // Fournisseurs
            'suppliers.view',
            'suppliers.create',
            'suppliers.update',
            'suppliers.delete',

            // Ventes
            'sales.view',
            'sales.create',
            'sales.update',
            'sales.delete',

            // Clients
            'customers.view',
            'customers.create',
            'customers.update',
            'customers.delete',

            // Retours
            'returns.view',
            'returns.create',
            'returns.update',

            // Rapports
            'reports.view',
            'reports.export',

            // Utilisateurs
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Rôles
        |--------------------------------------------------------------------------
        */

        $admin = Role::firstOrCreate([
            'name' => 'administrateur',
            'guard_name' => 'web',
        ]);

        $stockManager = Role::firstOrCreate([
            'name' => 'gestionnaire_stock',
            'guard_name' => 'web',
        ]);

        $cashier = Role::firstOrCreate([
            'name' => 'caissier',
            'guard_name' => 'web',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Administrateur
        |--------------------------------------------------------------------------
        */

        $admin->syncPermissions(
            Permission::all()
        );

        /*
        |--------------------------------------------------------------------------
        | Gestionnaire de stock
        |--------------------------------------------------------------------------
        */

        $stockManager->syncPermissions([
            'products.view',
            'products.create',
            'products.update',

            'categories.view',
            'categories.create',
            'categories.update',

            'brands.view',
            'brands.create',
            'brands.update',

            'sizes.view',
            'sizes.create',
            'sizes.update',

            'colors.view',
            'colors.create',
            'colors.update',

            'stock.view',
            'stock.adjust',
            'stock.manage',

            'purchases.view',
            'purchases.create',
            'purchases.update',

            'suppliers.view',
            'suppliers.create',
            'suppliers.update',

            'reports.view',
            'reports.export',

            'customers.view',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Caissier
        |--------------------------------------------------------------------------
        */

        $cashier->syncPermissions([
            'products.view',

            'categories.view',
            'brands.view',
            'sizes.view',
            'colors.view',

            'sales.view',
            'sales.create',

            'customers.view',
            'customers.create',
            'customers.update',

            'returns.view',
            'returns.create',
        ]);
    }
}