<?php

namespace App\Filament\Resources\ProductReturns\Pages;

use App\Enums\ReturnStatus;
use App\Filament\Resources\ProductReturns\ProductReturnResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateProductReturn extends CreateRecord
{
    protected static string $resource =
        ProductReturnResource::class;

    protected function mutateFormDataBeforeCreate(
        array $data
    ): array {

        $data['reference'] =
            'RET-' . now()->format('YmdHis');

        $data['status'] =
            ReturnStatus::PENDING->value;

        $data['user_id'] =
            Auth::id();

        return $data;
    }
}