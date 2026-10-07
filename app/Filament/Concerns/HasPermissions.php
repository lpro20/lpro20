<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Model;

trait HasPermissions
{
    public static function canViewAny(): bool
    {
        return static::hasResourcePermission('view');
    }

    public static function canCreate(): bool
    {
        return static::hasResourcePermission('create');
    }

    public static function canEdit(Model $record): bool
    {
        return static::hasResourcePermission('update');
    }

    public static function canDelete(Model $record): bool
    {
        return static::hasResourcePermission('delete');
    }

    public static function canDeleteAny(): bool
    {
        return static::hasResourcePermission('delete');
    }

    protected static function hasResourcePermission(string $action): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        $permissionPrefix = static::getPermissionPrefix();

        if (! $permissionPrefix) {
            return false;
        }

        return $user->hasPermissionTo(
            $permissionPrefix . '.' . $action
        );
    }

    protected static function getPermissionPrefix(): ?string
    {
        return match (static::class) {
            \App\Filament\Resources\Products\ProductResource::class => 'products',
            \App\Filament\Resources\Categories\CategoryResource::class => 'categories',
            \App\Filament\Resources\Brands\BrandResource::class => 'brands',
            \App\Filament\Resources\Sizes\SizeResource::class => 'sizes',
            \App\Filament\Resources\Colors\ColorResource::class => 'colors',
            \App\Filament\Resources\Purchases\PurchaseResource::class => 'purchases',
            \App\Filament\Resources\Suppliers\SupplierResource::class => 'suppliers',
            \App\Filament\Resources\Sales\SaleResource::class => 'sales',
            \App\Filament\Resources\Customers\CustomerResource::class => 'customers',
            \App\Filament\Resources\ProductReturns\ProductReturnResource::class => 'returns',
            \App\Filament\Resources\Users\UserResource::class => 'users',
            default => null,
        };
    }
}