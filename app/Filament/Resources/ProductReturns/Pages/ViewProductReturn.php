<?php

namespace App\Filament\Resources\ProductReturns\Pages;

use App\Filament\Resources\ProductReturns\ProductReturnResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProductReturn extends ViewRecord
{
    protected static string $resource = ProductReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
