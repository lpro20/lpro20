<?php

namespace App\Filament\Resources\Customers;

use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Models\Customer;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

use App\Filament\Concerns\HasPermissions;
class CustomerResource extends Resource
{
    use HasPermissions;

    // protected static ?string $permissionPrefix = 'customers';
    protected static ?string $model = Customer::class;

    protected static ?string $navigationLabel = 'Clients';

    protected static ?string $modelLabel = 'Client';

    protected static ?string $pluralModelLabel = 'Clients';

    protected static string|\UnitEnum|null $navigationGroup = 'Ventes';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom complet')
                    ->required()
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

                Toggle::make('is_active')
                    ->label('Client actif')
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
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('sales_count')
                    ->label('Ventes')
                    ->counts('sales')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),

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
            'index' => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'edit' => EditCustomer::route('/{record}/edit'),
        ];
    }
}