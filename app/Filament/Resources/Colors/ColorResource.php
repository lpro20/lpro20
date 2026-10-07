<?php

namespace App\Filament\Resources\Colors;

use App\Filament\Resources\Colors\Pages\CreateColor;
use App\Filament\Resources\Colors\Pages\EditColor;
use App\Filament\Resources\Colors\Pages\ListColors;
use App\Models\Color;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissions;

class ColorResource extends Resource
{
     use HasPermissions;

    // protected static ?string $permissionPrefix = 'colors';
    protected static ?string $model = Color::class;

    protected static ?string $navigationLabel = 'Couleurs';

    protected static ?string $modelLabel = 'couleur';

    protected static ?string $pluralModelLabel = 'couleurs';
           protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-swatch';

protected static string|\UnitEnum|null $navigationGroup = 'Catalogue';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                ColorPicker::make('hex_code')
                    ->label('Couleur'),

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

                ColorColumn::make('hex_code')
                    ->label('Couleur'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->defaultSort('name')
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListColors::route('/'),
            'create' => CreateColor::route('/create'),
            'edit' => EditColor::route('/{record}/edit'),
        ];
    }
}