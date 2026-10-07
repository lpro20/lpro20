<?php

namespace App\Filament\Resources\ProductVariants;

use App\Filament\Resources\ProductVariants\Pages\CreateProductVariant;
use App\Filament\Resources\ProductVariants\Pages\EditProductVariant;
use App\Filament\Resources\ProductVariants\Pages\ListProductVariants;
use App\Filament\Resources\ProductVariants\Pages\ViewProductVariant;
use App\Filament\Resources\ProductVariants\Schemas\ProductVariantForm;
use App\Filament\Resources\ProductVariants\Schemas\ProductVariantInfolist;
use App\Filament\Resources\ProductVariants\Tables\ProductVariantsTable;
use Filament\Tables\Columns\TextColumn;

use App\Models\ProductVariant;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProductVariantResource extends Resource
{
    protected static ?string $model = ProductVariant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'yes';

    public static function form(Schema $schema): Schema
    {
        return ProductVariantForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProductVariantInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product_id')
                  ->label('Produit')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn($record) => match (true) {
                        $record->stock_quantity === 0 => 'danger',
                        $record->stock_quantity <= $record->alert_threshold => 'warning',
                        default => 'success',
                    })
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductVariants::route('/'),
            'create' => CreateProductVariant::route('/create'),
            'view' => ViewProductVariant::route('/{record}'),
            'edit' => EditProductVariant::route('/{record}/edit'),
        ];
    }
}
