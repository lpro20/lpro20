<?php

namespace App\Filament\Resources\StockMovements;

// use App\Filament\Resources\StockMovements\Pages\ListStockMovements;
// use App\Models\StockMovement;
// use Filament\Resources\Resource;
// use Filament\Schemas\Schema;
// use Filament\Tables\Table;



use App\Enums\StockMovementType;
use App\Filament\Resources\StockMovements\Pages;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;

// use App\Filament\Concerns\HasPermissions;
class StockMovementResource extends Resource
{
//     use HasPermissions;

// protected static ?string $permissionPrefix = 'stock';
    protected static ?string $model = StockMovement::class;

    protected static ?string $navigationLabel = 'Mouvements de stock';

    protected static ?string $modelLabel = 'Mouvement de stock';

    protected static ?string $pluralModelLabel = 'Mouvements de stock';

    // protected static ?string $navigationGroup = 'Stock';
    protected static string|\UnitEnum|null $navigationGroup = 'Stock';


    // protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }




     public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('productVariant.product.name')
                    ->label('Produit')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('productVariant.sku')
                    ->label('SKU')
                    ->searchable(),

                TextColumn::make('productVariant.size.name')
                    ->label('Taille')
                    ->placeholder('-'),

                TextColumn::make('productVariant.color.name')
                    ->label('Couleur')
                    ->placeholder('-'),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(
                        fn (StockMovementType $state) => $state->label()
                    ),

                TextColumn::make('quantity')
                    ->label('Quantité')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('stock_before')
                    ->label('Avant')
                    ->numeric(),

                TextColumn::make('stock_after')
                    ->label('Après')
                    ->numeric(),

                TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('reason')
                    ->label('Motif')
                    ->limit(40)
                    ->tooltip(
                        fn (StockMovement $record) => $record->reason
                    )
                    ->placeholder('-'),
            ])

            ->filters([

                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options(
                        collect(StockMovementType::cases())
                            ->mapWithKeys(
                                fn (StockMovementType $type) =>
                                    [$type->value => $type->label()]
                            )
                            ->toArray()
                    ),

                Tables\Filters\SelectFilter::make('product_variant_id')
                    ->label('Produit / Variante')
                    ->options(
                        ProductVariant::query()
                            ->with(['product', 'size', 'color'])
                            ->get()
                            ->mapWithKeys(function (
                                ProductVariant $variant
                            ) {

                                $label = $variant->product->name;

                                if ($variant->size) {
                                    $label .=
                                        ' - Taille ' .
                                        $variant->size->name;
                                }

                                if ($variant->color) {
                                    $label .=
                                        ' - ' .
                                        $variant->color->name;
                                }

                                return [
                                    $variant->id => $label
                                ];
                            })
                            ->toArray()
                    )
                    ->searchable(),

            ])

            ->defaultSort('created_at', 'desc')

            ->recordActions([])

            ->toolbarActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockMovements::route('/'),
        ];
    }




//         public static function canAccess(): bool
// {
//     return auth()->check()
//         && auth()->user()->hasPermissionTo('stock.view');
// }


public static function canCreate(): bool
{
    return false;
}

public static function canEdit($record): bool
{
    return false;
}

public static function canDelete($record): bool
{
    return false;
}
}