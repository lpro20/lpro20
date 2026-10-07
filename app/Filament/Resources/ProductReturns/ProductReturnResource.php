<?php

namespace App\Filament\Resources\ProductReturns;

use App\Enums\ReturnReason;
use App\Enums\ReturnStatus;
use App\Filament\Resources\ProductReturns\Pages;
use App\Models\ProductReturn;
use App\Models\Sale;
use App\Models\SaleItem;
// use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;

use App\Filament\Concerns\HasPermissions;
class ProductReturnResource extends Resource
{
    use HasPermissions;

    // protected static ?string $permissionPrefix = 'returns';
    protected static ?string $model = ProductReturn::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-uturn-left';

    protected static string|\UnitEnum|null $navigationGroup = 'Ventes';

    protected static ?string $navigationLabel = 'Retours';

    protected static ?string $modelLabel = 'Retour';

    protected static ?string $pluralModelLabel = 'Retours';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Vente concernée')
                    ->schema([

                        Select::make('sale_id')
                            ->label('Vente')
                            ->options(
                                Sale::query()
                                    ->where(
                                        'status',
                                        'completed'
                                    )
                                    ->orderByDesc('sale_date')
                                    ->get()
                                    ->mapWithKeys(
                                        fn(Sale $sale) => [
                                            $sale->id =>
                                            $sale->reference
                                                . ' — '
                                                . number_format(
                                                    $sale->total,
                                                    0,
                                                    ',',
                                                    ' '
                                                )
                                                . ' FCFA',
                                        ]
                                    )
                                    ->toArray()
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(
                                function (
                                    $state,
                                    Set $set
                                ) {
                                    $set(
                                        'sale_item_id',
                                        null
                                    );

                                    $set(
                                        'product_variant_id',
                                        null
                                    );

                                    $set('quantity', 1);
                                }
                            )
                            ->required()
                            ->columnSpanFull(),

                        Select::make('sale_item_id')
                            ->label('Article retourné')
                            ->options(
                                function (Get $get) {

                                    $saleId = $get('sale_id');

                                    if (!$saleId) {
                                        return [];
                                    }

                                    return SaleItem::query()
                                        ->with([
                                            'productVariant.product',
                                            'productVariant.size',
                                            'productVariant.color',
                                        ])
                                        ->where('sale_id', $saleId)
                                        ->get()
                                        ->mapWithKeys(function (SaleItem $item) {

                                            $variant = $item->productVariant;

                                            $label = $variant->product->name;

                                            if ($variant->size) {
                                                $label .=
                                                    ' - Taille ' . $variant->size->name;
                                            }

                                            if ($variant->color) {
                                                $label .=
                                                    ' - ' . $variant->color->name;
                                            }

                                            $alreadyReturned = ProductReturn::query()
                                                ->where(
                                                    'sale_item_id',
                                                    $item->id
                                                )
                                                ->where(
                                                    'status',
                                                    ReturnStatus::COMPLETED->value
                                                )
                                                ->sum('quantity');

                                            $remaining =
                                                (int) $item->quantity
                                                - (int) $alreadyReturned;

                                            $label .=
                                                " — restant : {$remaining}";

                                            return [
                                                $item->id => $label,
                                            ];
                                        })
                                        ->toArray();
                                }
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (
                                $state,
                                Get $get,
                                Set $set
                            ) {

                                if (!$state) {
                                    $set('product_variant_id', null);
                                    $set('quantity', 1);

                                    return;
                                }

                                $saleItem = SaleItem::with(
                                    'productVariant'
                                )->find($state);

                                if (!$saleItem) {
                                    return;
                                }

                                $set(
                                    'product_variant_id',
                                    $saleItem->product_variant_id
                                );

                                $set('quantity', 1);
                            })
                            ->required()
                            ->disabled(
                                fn(Get $get) => !$get('sale_id')
                            ),

                        Select::make('product_variant_id')
                            ->label('Produit / Variante')
                            ->options(
                                function (Get $get) {

                                    $variantId =
                                        $get('product_variant_id');

                                    if (!$variantId) {
                                        return [];
                                    }

                                    $item =
                                        SaleItem::with([
                                            'productVariant.product',
                                            'productVariant.size',
                                            'productVariant.color',
                                        ])->find($get(
                                            'sale_item_id'
                                        ));

                                    if (!$item) {
                                        return [];
                                    }

                                    $variant =
                                        $item->productVariant;

                                    $label =
                                        $variant->product->name;

                                    if ($variant->size) {
                                        $label .=
                                            ' - Taille '
                                            . $variant->size->name;
                                    }

                                    if ($variant->color) {
                                        $label .=
                                            ' - '
                                            . $variant->color->name;
                                    }

                                    return [
                                        $variant->id => $label,
                                    ];
                                }
                            )
                            ->disabled()
                            ->dehydrated()
                            ->required(),

                    ])
                    ->columns(2),

                Section::make('Détails du retour')
                    ->schema([

                        TextInput::make('quantity')
                            ->label('Quantité retournée')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->default(1)
                            ->live()
                            ->maxValue(function (Get $get): int {

                                $saleItemId = $get('sale_item_id');

                                if (!$saleItemId) {
                                    return 1;
                                }

                                $saleItem = SaleItem::find($saleItemId);

                                if (!$saleItem) {
                                    return 1;
                                }

                                $alreadyReturned = ProductReturn::query()
                                    ->where('sale_item_id', $saleItemId)
                                    ->where(
                                        'status',
                                        ReturnStatus::COMPLETED->value
                                    )
                                    ->sum('quantity');

                                $remaining = (int) $saleItem->quantity
                                    - (int) $alreadyReturned;

                                return max(1, $remaining);
                            })
                            ->helperText(function (Get $get): string {

                                $saleItemId = $get('sale_item_id');

                                if (!$saleItemId) {
                                    return 'Sélectionnez d’abord l’article concerné.';
                                }

                                $saleItem = SaleItem::find($saleItemId);

                                if (!$saleItem) {
                                    return 'Article introuvable.';
                                }

                                $alreadyReturned = ProductReturn::query()
                                    ->where('sale_item_id', $saleItemId)
                                    ->where(
                                        'status',
                                        ReturnStatus::COMPLETED->value
                                    )
                                    ->sum('quantity');

                                $remaining = (int) $saleItem->quantity
                                    - (int) $alreadyReturned;

                                return "Quantité maximale retournable : {$remaining}";
                            })
                            ->required(),

                        Select::make('reason')
                            ->label('Motif du retour')
                            ->options(
                                collect(
                                    ReturnReason::cases()
                                )->mapWithKeys(
                                    fn(
                                        ReturnReason $reason
                                    ) => [
                                        $reason->value =>
                                        $reason->label(),
                                    ]
                                )->toArray()
                            )
                            ->required(),

                        Toggle::make('restock')
                            ->label('Remettre en stock')
                            ->helperText(
                                'Désactivez cette option si '
                                    . 'le produit est endommagé ou '
                                    . 'ne peut plus être vendu.'
                            )
                            ->default(true)
                            ->live(),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->columnSpanFull(),

                    ])
                    ->columns(2),

            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {

        return $table
            ->columns([

                TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable()
                    ->sortable(),

                TextColumn::make(
                    'sale.reference'
                )
                    ->label('Vente')
                    ->searchable(),

                TextColumn::make(
                    'productVariant.product.name'
                )
                    ->label('Produit')
                    ->searchable(),

                TextColumn::make(
                    'productVariant.size.name'
                )
                    ->label('Taille')
                    ->placeholder('-'),

                TextColumn::make(
                    'productVariant.color.name'
                )
                    ->label('Couleur')
                    ->placeholder('-'),

                TextColumn::make('quantity')
                    ->label('Qté')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('reason')
                    ->label('Motif')
                    ->badge()
                    ->formatStateUsing(
                        fn(
                            ReturnReason $state
                        ) => $state->label()
                    ),

                TextColumn::make('restock')
                    ->label('Stock')
                    ->badge()
                    ->formatStateUsing(
                        fn(bool $state) =>
                        $state
                            ? 'Remis en stock'
                            : 'Non revendable'
                    )
                    ->color(
                        fn(bool $state) =>
                        $state
                            ? 'success'
                            : 'danger'
                    ),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(
                        fn(
                            ReturnStatus $state
                        ) => $state->label()
                    ),

                TextColumn::make('user.name')
                    ->label('Traité par')
                    ->placeholder('-'),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

            ])

            ->filters([

                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options(
                        collect(
                            ReturnStatus::cases()
                        )->mapWithKeys(
                            fn(
                                ReturnStatus $status
                            ) => [
                                $status->value =>
                                $status->label(),
                            ]
                        )->toArray()
                    ),

                Tables\Filters\SelectFilter::make('reason')
                    ->label('Motif')
                    ->options(
                        collect(
                            ReturnReason::cases()
                        )->mapWithKeys(
                            fn(
                                ReturnReason $reason
                            ) => [
                                $reason->value =>
                                $reason->label(),
                            ]
                        )->toArray()
                    ),

            ])

            ->defaultSort(
                'created_at',
                'desc'
            )

            ->recordActions([
                EditAction::make()
                    ->visible(
                        fn(ProductReturn $record) =>
                        $record->status === ReturnStatus::PENDING
                    ),

                DeleteAction::make()
                    ->visible(
                        fn(ProductReturn $record) =>
                        $record->status === ReturnStatus::PENDING
                    ),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' =>
            Pages\ListProductReturns::route('/'),

            'create' =>
            Pages\CreateProductReturn::route('/create'),

            'edit' =>
            Pages\EditProductReturn::route('/{record}/edit'),
        ];
    }
}
