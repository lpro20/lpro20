<?php

namespace App\Filament\Resources\Suppliers;

use App\Filament\Resources\Suppliers\Pages\CreateSupplier;
use App\Filament\Resources\Suppliers\Pages\EditSupplier;
use App\Filament\Resources\Suppliers\Pages\ListSuppliers;
use App\Models\Supplier;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

use App\Filament\Concerns\HasPermissions;

class SupplierResource extends Resource
{
    use HasPermissions;

    // protected static ?string $permissionPrefix = 'suppliers';
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationLabel = 'Fournisseurs';

    protected static ?string $modelLabel = 'Fournisseur';

    protected static ?string $pluralModelLabel = 'Fournisseurs';

    protected static string|\UnitEnum|null $navigationGroup = 'Achats';

    // protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-truck';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom du contact')
                    ->required()
                    ->maxLength(255),

                TextInput::make('company')
                    ->label('Entreprise')
                    ->maxLength(255),

                TextInput::make('phone')
                    ->label('Téléphone')
                    ->tel()
                    ->maxLength(50),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255),

                TextInput::make('address')
                    ->label('Adresse')
                    ->maxLength(255),

                TextInput::make('city')
                    ->label('Ville')
                    ->maxLength(100),

                TextInput::make('country')
                    ->label('Pays')
                    ->default('Cameroun')
                    ->maxLength(100)
                    ->required(),

                Toggle::make('is_active')
                    ->label('Fournisseur actif')
                    ->default(true),

                Textarea::make('notes')
                    ->label('Notes')
                    ->rows(4)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Contact')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('company')
                    ->label('Entreprise')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('city')
                    ->label('Ville')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),

                TextColumn::make('purchases_count')
                    ->label('Achats')
                    ->counts('purchases')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Statut')
                    ->trueLabel('Actifs uniquement')
                    ->falseLabel('Inactifs uniquement')
                    ->placeholder('Tous'),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make(),
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
            'index' => ListSuppliers::route('/'),
            'create' => CreateSupplier::route('/create'),
            'edit' => EditSupplier::route('/{record}/edit'),
        ];
    }
}