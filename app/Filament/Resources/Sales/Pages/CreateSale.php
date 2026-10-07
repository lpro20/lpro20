<?php

// namespace App\Filament\Resources\Sales\Pages;

// use App\Filament\Resources\Sales\SaleResource;
// use Filament\Resources\Pages\CreateRecord;

// class CreateSale extends CreateRecord
// {
//     protected static string $resource = SaleResource::class;
// }





namespace App\Filament\Resources\Sales\Pages;

use App\Enums\SaleStatus;
use App\Filament\Resources\Sales\SaleResource;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

        $data['status'] = SaleStatus::DRAFT->value;

        return $data;
    }
}