<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Size;
use App\Models\Color;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use App\Filament\Concerns\HasPermissions;
class ProductResource extends Resource
{
     use HasPermissions;

     protected static ?string $model = Product::class;
    //  protected static ?string $permissionPrefix = 'products';

    protected static ?string $navigationLabel = 'Produits';

    protected static ?string $modelLabel = 'produit';

    protected static ?string $pluralModelLabel = 'produits';

    // protected static ?string $navigationGroup = 'Catalogue';
protected static string|\UnitEnum|null $navigationGroup = 'Catalogue';
protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cube';
    protected static ?int $navigationSort = 0;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Informations générales')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom du produit')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        TextInput::make('sku')
                            ->label('SKU')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(100),

                        Select::make('category_id')
                            ->label('Catégorie')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('brand_id')
                            ->label('Marque')
                            ->relationship('brand', 'name')
                            ->searchable()
                            ->preload(),

                        Select::make('gender')
                            ->label('Genre')
                            ->options([
                                'men' => 'Homme',
                                'women' => 'Femme',
                                'children' => 'Enfant',
                                'unisex' => 'Unisexe',
                            ])
                            ->native(false),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(4)
                            ->columnSpanFull(),

                        FileUpload::make('image')
                            ->label('Image principale')
                            ->image()
                            ->disk('public')
                            ->directory('products')
                            ->imageEditor()
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->label('Produit actif')
                            ->default(true),
                    ])
                    ->columns(2),

                Section::make('Variantes')
                    ->description('Définissez les tailles, couleurs et stocks du produit.')
                    ->schema([
                        Repeater::make('variants')
                            ->relationship()
                            ->schema([
                                Select::make('size_id')
                                    ->label('Taille / Pointure')
                                    ->relationship(
                                        name: 'size',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn ($query) =>
                                            $query->where('is_active', true)
                                    )
                                    ->searchable()
                                    ->preload(),

                                Select::make('color_id')
                                    ->label('Couleur')
                                    ->relationship(
                                        name: 'color',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn ($query) =>
                                            $query->where('is_active', true)
                                    )
                                    ->searchable()
                                    ->preload(),

                                TextInput::make('sku')
                                    ->label('SKU variante')
                                    ->required()
                                    ->maxLength(100),

                                TextInput::make('purchase_price')
                                    ->label("Prix d'achat")
                                    ->numeric()
                                    ->minValue(0)
                                    ->required()
                                    ->suffix('FCFA'),

                                TextInput::make('selling_price')
                                    ->label('Prix de vente')
                                    ->numeric()
                                    ->minValue(0)
                                    ->required()
                                    ->suffix('FCFA'),

                                // TextInput::make('stock_quantity')
                                //     ->label('Stock initial')
                                //     ->numeric()
                                //     ->integer()
                                //     ->minValue(0)
                                //     ->default(0)
                                //     ->required(),

                                TextInput::make('alert_threshold')
                                    ->label("Seuil d'alerte")
                                    ->numeric()
                                    ->integer()
                                    ->minValue(0)
                                    ->default(5)
                                    ->required(),

                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true),
                            ])
                            ->columns(2)
                            ->defaultItems(1)
                            ->addActionLabel('Ajouter une variante')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Produit')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),

                TextColumn::make('category.name')
                    ->label('Catégorie')
                    ->sortable(),

                TextColumn::make('brand.name')
                    ->label('Marque')
                    ->sortable(),

                TextColumn::make('gender')
                    ->label('Genre')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'men' => 'Homme',
                        'women' => 'Femme',
                        'children' => 'Enfant',
                        'unisex' => 'Unisexe',
                        default => '-',
                    }),

                TextColumn::make('variants_count')
                    ->label('Variantes')
                    ->counts('variants'),

                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}