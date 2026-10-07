<?php

// namespace App\Filament\Resources\Brands;

// use App\Filament\Resources\Brands\Pages\CreateBrand;
// use App\Filament\Resources\Brands\Pages\EditBrand;
// use App\Filament\Resources\Brands\Pages\ListBrands;
// use App\Filament\Resources\Brands\Pages\ViewBrand;
// use App\Filament\Resources\Brands\Schemas\BrandForm;
// use App\Filament\Resources\Brands\Schemas\BrandInfolist;
// use App\Filament\Resources\Brands\Tables\BrandsTable;
// use App\Models\Brand;
// use BackedEnum;
// use Filament\Resources\Resource;
// use Filament\Schemas\Schema;
// use Filament\Support\Icons\Heroicon;
// use Filament\Tables\Table;

// class BrandResource extends Resource
// {
//     protected static ?string $model = Brand::class;

//     protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

//     protected static ?string $recordTitleAttribute = 'name';

//     public static function form(Schema $schema): Schema
//     {
//         return BrandForm::configure($schema);
//     }

//     public static function infolist(Schema $schema): Schema
//     {
//         return BrandInfolist::configure($schema);
//     }

//     public static function table(Table $table): Table
//     {
//         return BrandsTable::configure($table);
//     }

//     public static function getRelations(): array
//     {
//         return [
//             //
//         ];
//     }

//     public static function getPages(): array
//     {
//         return [
//             'index' => ListBrands::route('/'),
//             'create' => CreateBrand::route('/create'),
//             'view' => ViewBrand::route('/{record}'),
//             'edit' => EditBrand::route('/{record}/edit'),
//         ];
//     }
// }





namespace App\Filament\Resources\Brands;

use App\Filament\Resources\Brands\Pages\CreateBrand;
use App\Filament\Resources\Brands\Pages\EditBrand;
use App\Filament\Resources\Brands\Pages\ListBrands;
use App\Models\Brand;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissions;
class BrandResource extends Resource
{
    use HasPermissions;

// protected static ?string $permissionPrefix = 'brands';
    protected static ?string $model = Brand::class;

    protected static ?string $navigationLabel = 'Marques';

    protected static ?string $modelLabel = 'marque';

    protected static ?string $pluralModelLabel = 'marques';

   protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bookmark';

protected static string|\UnitEnum|null $navigationGroup = 'Catalogue';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Textarea::make('description')
                    ->label('Description')
                    ->rows(4),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Créée le')
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
            'index' => ListBrands::route('/'),
            'create' => CreateBrand::route('/create'),
            'edit' => EditBrand::route('/{record}/edit'),
        ];
    }
}