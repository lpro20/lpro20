<?php

namespace App\Filament\Resources\Purchases;

use App\Enums\PurchaseStatus;
use App\Filament\Resources\Purchases\Pages\CreatePurchase;
use App\Filament\Resources\Purchases\Pages\EditPurchase;
use App\Filament\Resources\Purchases\Pages\ListPurchases;
use App\Models\ProductVariant;
use App\Models\Purchase;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

use App\Filament\Concerns\HasPermissions;
class PurchaseResource extends Resource
{
    use HasPermissions;

// protected static ?string $permissionPrefix = 'purchases';
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationLabel = 'Achats';

    protected static ?string $modelLabel = 'Achat';

    protected static ?string $pluralModelLabel = 'Achats';

    protected static string|\UnitEnum|null $navigationGroup = 'Achats';

    // protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-truck';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Informations générales')
                    ->schema([
                        Select::make('supplier_id')
                            ->label('Fournisseur')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        DatePicker::make('purchase_date')
                            ->label('Date d’achat')
                            ->default(now())
                            ->required(),

                        TextInput::make('reference')
                            ->label('Référence')
                            ->default(fn() => 'ACH-' . now()->format('YmdHis'))
                            ->required()
                            ->maxLength(255),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Section::make('Lignes d’achat')
                    ->schema([
                        Repeater::make('items')
                            ->label('')
                            ->relationship()
                            ->live()
                            ->schema([

                                Select::make('product_variant_id')
                                    ->label('Produit / Variante')
                                    ->options(
                                        ProductVariant::query()
                                            ->with(['product', 'size', 'color'])
                                            ->where('is_active', true)
                                            ->get()
                                            ->mapWithKeys(function (ProductVariant $variant) {

                                                $label = $variant->product->name;

                                                if ($variant->size) {
                                                    $label .= ' - Taille ' . $variant->size->name;
                                                }

                                                if ($variant->color) {
                                                    $label .= ' - ' . $variant->color->name;
                                                }

                                                $label .= ' [' . $variant->sku . ']';

                                                return [
                                                    $variant->id => $label
                                                ];
                                            })
                                            ->toArray()
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
                                            $set('unit_price', 0);
                                            $set('total', 0);

                                            return;
                                        }

                                        $variant = ProductVariant::find($state);

                                        if (!$variant) {
                                            return;
                                        }

                                        $unitPrice = (float) $variant->purchase_price;

                                        $quantity = (int) ($get('quantity') ?? 1);

                                        $set('unit_price', $unitPrice);

                                        $set(
                                            'total',
                                            $quantity * $unitPrice
                                        );
                                    })
                                    ->required()
                                    ->columnSpan(2),

                                TextInput::make('quantity')
                                    ->label('Quantité')
                                    ->numeric()
                                    ->integer()
                                    ->minValue(1)
                                    ->default(1)
                                    ->live()
                                    ->afterStateUpdated(function (
                                        $state,
                                        Get $get,
                                        Set $set
                                    ) {

                                        $quantity = (int) ($state ?? 0);

                                        $unitPrice = (float) (
                                            $get('unit_price') ?? 0
                                        );

                                        $set(
                                            'total',
                                            $quantity * $unitPrice
                                        );
                                    })
                                    ->required(),

                                TextInput::make('unit_price')
                                    ->label('Prix unitaire')
                                    ->numeric()
                                    ->minValue(0)
                                    ->suffix('FCFA')
                                    ->live()
                                    ->afterStateUpdated(function (
                                        $state,
                                        Get $get,
                                        Set $set
                                    ) {

                                        $unitPrice = (float) ($state ?? 0);

                                        $quantity = (int) (
                                            $get('quantity') ?? 0
                                        );

                                        $set(
                                            'total',
                                            $quantity * $unitPrice
                                        );
                                    })
                                    ->required(),

                                TextInput::make('total')
                                    ->label('Total ligne')
                                    ->numeric()
                                    ->disabled()
                                    ->default(0)
                                    ->dehydrated()
                                    ->suffix('FCFA')
                                    ->required(),

                            ])
                            ->columns(2)
                            ->defaultItems(1)
                            ->addActionLabel('Ajouter une ligne')
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ]),

                Section::make('Totaux')
                    ->schema([

                        TextInput::make('subtotal')
                            ->label('Sous-total')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->default(0)
                            ->suffix('FCFA'),

                        TextInput::make('discount')
                            ->label('Remise')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(function (
                                $state,
                                Get $get,
                                Set $set
                            ) {
                                self::calculatePurchaseTotal($get, $set);
                            })
                            ->suffix('FCFA'),

                        TextInput::make('shipping_cost')
                            ->label('Frais de livraison')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(function (
                                $state,
                                Get $get,
                                Set $set
                            ) {
                                self::calculatePurchaseTotal($get, $set);
                            })
                            ->suffix('FCFA'),

                        TextInput::make('total')
                            ->label('Total général')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->default(0)
                            ->suffix('FCFA')
                            ->extraInputAttributes([
                                'class' => 'font-bold',
                            ]),

                    ])
                    ->columns(2),

            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('supplier.name')
                    ->label('Fournisseur')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('purchase_date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(
                        fn($state) => $state instanceof PurchaseStatus
                            ? $state->label()
                            : PurchaseStatus::from($state)->label()
                    ),

                TextColumn::make('total')
                    ->label('Total')
                    ->numeric()
                    ->suffix(' FCFA')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Créé par')
                    ->default('-'),

                TextColumn::make('items_count')
                    ->label('Articles')
                    ->counts('items')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(
                        collect(PurchaseStatus::cases())
                            ->mapWithKeys(
                                fn(PurchaseStatus $status) => [
                                    $status->value => $status->label(),
                                ]
                            )
                            ->toArray()
                    ),

                SelectFilter::make('supplier_id')
                    ->label('Fournisseur')
                    ->relationship('supplier', 'name'),
            ])
            // ->recordActions([
            //     \Filament\Actions\EditAction::make(),
            // ])
            ->recordActions([
                \Filament\Actions\EditAction::make()
                    ->visible(
                        fn(Purchase $record) =>
                        $record->status === PurchaseStatus::DRAFT
                    ),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPurchases::route('/'),
            'create' => CreatePurchase::route('/create'),
            'edit' => EditPurchase::route('/{record}/edit'),
        ];
    }


    protected static function calculatePurchaseTotal(
        Get $get,
        Set $set
    ): void {
        $items = $get('items') ?? [];

        $subtotal = 0;

        foreach ($items as $item) {

            $quantity = (float) ($item['quantity'] ?? 0);

            $unitPrice = (float) ($item['unit_price'] ?? 0);

            $subtotal += $quantity * $unitPrice;
        }

        $discount = (float) ($get('discount') ?? 0);

        $shippingCost = (float) ($get('shipping_cost') ?? 0);

        $total = $subtotal - $discount + $shippingCost;

        if ($total < 0) {
            $total = 0;
        }

        $set('subtotal', $subtotal);

        $set('total', $total);
    }
}
