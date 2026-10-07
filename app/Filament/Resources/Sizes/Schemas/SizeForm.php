<?php

namespace App\Filament\Resources\Sizes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SizeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('type')
                    ->options(['clothing' => 'Clothing', 'shoes' => 'Shoes', 'other' => 'Other'])
                    ->default('clothing')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
