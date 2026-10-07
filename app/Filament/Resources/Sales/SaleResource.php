<?php

namespace App\Filament\Resources\Sales;

use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use App\Filament\Resources\Sales\Pages\CreateSale;
use App\Filament\Resources\Sales\Pages\EditSale;
use App\Filament\Resources\Sales\Pages\ListSales;
use App\Models\ProductVariant;
use App\Models\Sale;
use Filament\Forms\Components\DateTimePicker;
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
class SaleResource extends Resource
{
    use HasPermissions;

// protected static ?string $permissionPrefix = 'sales';
    protected static ?string $model = Sale::class;

    protected static ?string $navigationLabel = 'Ventes';

    protected static ?string $modelLabel = 'Vente';

    protected static ?string $pluralModelLabel = 'Ventes';

    protected static string|\UnitEnum|null $navigationGroup = 'Ventes';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';
    // protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Informations générales')
                    ->schema([

                        Select::make('customer_id')
                            ->label('Client')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Client de passage'),

                        DateTimePicker::make('sale_date')
                            ->label('Date de vente')
                            ->default(now())
                            ->required(),

                        TextInput::make('reference')
                            ->label('Référence')
                            ->default(
                                fn() => 'VTE-' . now()->format('YmdHis')
                            )
                            ->required()
                            ->unique(ignoreRecord: true),

                        Select::make('payment_method')
                            ->label('Mode de paiement')
                            ->options(
                                collect(PaymentMethod::cases())
                                    ->mapWithKeys(
                                        fn(PaymentMethod $method) => [
                                            $method->value => $method->label(),
                                        ]
                                    )
                                    ->toArray()
                            )
                            ->default(PaymentMethod::CASH->value)
                            ->required(),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(4),

                Section::make('Produits vendus')
                    ->schema([

                        Repeater::make('items')
                            ->relationship()
                            ->label('Articles')
                            ->schema([
                                Select::make('product_variant_id')
                                    ->label('Produit / Variante')
                                    ->options(
                                        ProductVariant::query()
                                            ->with([
                                                'product',
                                                'size',
                                                'color',
                                            ])
                                            ->where('is_active', true)
                                            ->get()
                                            ->mapWithKeys(
                                                function (ProductVariant $variant) {
                                                    $label = $variant->product->name;

                                                    if ($variant->size) {
                                                        $label .= ' - Taille ' . $variant->size->name;
                                                    }

                                                    if ($variant->color) {
                                                        $label .= ' - ' . $variant->color->name;
                                                    }

                                                    $label .= ' [' . $variant->sku . ']';

                                                    return [
                                                        $variant->id => $label,
                                                    ];
                                                }
                                            )
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

                                        $unitPrice = (float) $variant->selling_price;
                                        $quantity = (int) ($get('quantity') ?? 1);

                                        $set('unit_price', $unitPrice);
                                        $set('total', $quantity * $unitPrice);
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
                                        $unitPrice = (float) ($get('unit_price') ?? 0);

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
                                        Get $get,
                                        Set $set
                                    ) {
                                        $quantity = (int) ($get('quantity') ?? 0);
                                        $unitPrice = (float) ($get('unit_price') ?? 0);

                                        $set(
                                            'total',
                                            $quantity * $unitPrice
                                        );
                                    })
                                    ->required(),
                                TextInput::make('total')
                                    ->label('Total')
                                    ->numeric()
                                    ->disabled()
                                    ->suffix('FCFA'),
                            ])
                            ->columns(2)
                            ->minItems(1)
                            ->defaultItems(1)
                            ->addActionLabel('Ajouter un article')
                            ->reorderable(false)
                            ->required(),
                    ]),

                Section::make('Paiement')
                    ->schema([

                        TextInput::make('subtotal')
                            ->label('Sous-total')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false)
                            ->suffix('FCFA'),

                        TextInput::make('discount')
                            ->label('Remise')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->live()
                            ->afterStateUpdated(function (
                                Get $get,
                                Set $set
                            ) {
                                self::updateSaleTotals($get, $set);
                            })
                            ->suffix('FCFA'),

                        TextInput::make('total')
                            ->label('Total à payer')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false)
                            ->suffix('FCFA'),

                        TextInput::make('amount_paid')
                            ->label('Montant reçu')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->live()
                            ->afterStateUpdated(function (
                                Get $get,
                                Set $set
                            ) {
                                self::updateChange($get, $set);
                            })
                            ->suffix('FCFA')
                            ->required(),

                        TextInput::make('change_amount')
                            ->label('Monnaie à rendre')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false)
                            ->suffix('FCFA'),

                    ])
                    ->columns(3),
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

                TextColumn::make('customer.name')
                    ->label('Client')
                    ->searchable()
                    ->placeholder('Client de passage'),

                TextColumn::make('sale_date')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(
                        fn($state) => $state instanceof SaleStatus
                            ? $state->label()
                            : SaleStatus::from($state)->label()
                    ),

                TextColumn::make('payment_method')
                    ->label('Paiement')
                    ->formatStateUsing(
                        fn($state) => $state instanceof PaymentMethod
                            ? $state->label()
                            : PaymentMethod::from($state)->label()
                    ),

                TextColumn::make('total')
                    ->label('Total')
                    ->numeric()
                    ->suffix(' FCFA')
                    ->sortable(),

                TextColumn::make('amount_paid')
                    ->label('Payé')
                    ->numeric()
                    ->suffix(' FCFA'),

                TextColumn::make('change_amount')
                    ->label('Monnaie')
                    ->numeric()
                    ->suffix(' FCFA'),

                TextColumn::make('user.name')
                    ->label('Caissier')
                    ->default('-'),

            ])
            ->filters([

                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(
                        collect(SaleStatus::cases())
                            ->mapWithKeys(
                                fn(SaleStatus $status) => [
                                    $status->value => $status->label(),
                                ]
                            )
                            ->toArray()
                    ),

                SelectFilter::make('payment_method')
                    ->label('Paiement')
                    ->options(
                        collect(PaymentMethod::cases())
                            ->mapWithKeys(
                                fn(PaymentMethod $method) => [
                                    $method->value => $method->label(),
                                ]
                            )
                            ->toArray()
                    ),

                SelectFilter::make('customer_id')
                    ->label('Client')
                    ->relationship('customer', 'name'),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make()
                    ->visible(
                        fn(Sale $record) =>
                        $record->status === SaleStatus::DRAFT
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
            'index' => ListSales::route('/'),
            'create' => CreateSale::route('/create'),
            'edit' => EditSale::route('/{record}/edit'),
        ];
    }













    public static function updateSaleTotals(
        Get $get,
        Set $set
    ): void {
        $items = $get('items') ?? [];

        $subtotal = 0;

        foreach ($items as $item) {
            $quantity = (int) ($item['quantity'] ?? 0);
            $unitPrice = (float) ($item['unit_price'] ?? 0);

            $subtotal += $quantity * $unitPrice;
        }

        $discount = (float) ($get('discount') ?? 0);

        if ($discount > $subtotal) {
            $discount = $subtotal;
        }

        $total = $subtotal - $discount;

        $set('subtotal', $subtotal);
        $set('total', $total);

        self::updateChange($get, $set);
    }



    public static function updateChange(
        Get $get,
        Set $set
    ): void {
        $items = $get('items') ?? [];

        $subtotal = 0;

        foreach ($items as $item) {
            $quantity = (int) ($item['quantity'] ?? 0);
            $unitPrice = (float) ($item['unit_price'] ?? 0);

            $subtotal += $quantity * $unitPrice;
        }

        $discount = (float) ($get('discount') ?? 0);

        $total = max(
            0,
            $subtotal - $discount
        );

        $amountPaid = (float) ($get('amount_paid') ?? 0);

        $change = max(
            0,
            $amountPaid - $total
        );

        $set('subtotal', $subtotal);
        $set('total', $total);
        $set('change_amount', $change);
    }
}
