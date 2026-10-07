<?php

namespace App\Filament\Resources\Sizes;

use App\Filament\Resources\Sizes\Pages\CreateSize;
use App\Filament\Resources\Sizes\Pages\EditSize;
use App\Filament\Resources\Sizes\Pages\ListSizes;
use App\Models\Size;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use App\Filament\Concerns\HasPermissions;

class SizeResource extends Resource
{
    use HasPermissions;

    // protected static ?string $permissionPrefix = 'sizes';
    protected static ?string $model = Size::class;

    protected static ?string $navigationLabel = 'Tailles';

    protected static ?string $modelLabel = 'taille';

    protected static ?string $pluralModelLabel = 'tailles';

       protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrows-pointing-out';

protected static string|\UnitEnum|null $navigationGroup = 'Catalogue';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Taille / Pointure')
                    ->required()
                    ->maxLength(50),

                Select::make('type')
                    ->label('Type')
                    ->options([
                        'clothing' => 'Vêtement',
                        'shoes' => 'Chaussure',
                        'other' => 'Autre',
                    ])
                    ->required()
                    ->native(false),

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
                    ->label('Taille')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'clothing' => 'Vêtement',
                        'shoes' => 'Chaussure',
                        default => 'Autre',
                    }),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
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
            'index' => ListSizes::route('/'),
            'create' => CreateSize::route('/create'),
            'edit' => EditSize::route('/{record}/edit'),
        ];
    }
}