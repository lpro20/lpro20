<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
// use Filament\Forms\Form;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;

use App\Filament\Concerns\HasPermissions;
class UserResource extends Resource
{
    use HasPermissions;
    // protected static ?string $permissionPrefix = 'users';
    protected static ?string $model = User::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Utilisateurs';

    protected static ?string $modelLabel = 'utilisateur';

    protected static ?string $pluralModelLabel = 'utilisateurs';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 1;


    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->label('Nom complet')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ex. Jean Dupont'),

                TextInput::make('email')
                    ->label('Adresse e-mail')
                    ->email()
                    ->required()
                    ->unique(
                        table: 'users',
                        column: 'email',
                        ignoreRecord: true
                    )
                    ->maxLength(255),

                Select::make('role')
                    ->label('Rôle')
                    ->options(
                        Role::query()
                            ->orderBy('name')
                            ->pluck('name', 'name')
                            ->mapWithKeys(fn($name) => [
                                $name => match ($name) {
                                    'administrateur' => 'Administrateur',
                                    'gestionnaire_stock' => 'Gestionnaire de stock',
                                    'caissier' => 'Caissier',
                                    default => ucfirst(str_replace('_', ' ', $name)),
                                },
                            ])
                            ->toArray()
                    )
                    ->required()
                    ->searchable()
                    ->preload(),

                TextInput::make('password')
                    ->label('Mot de passe')
                    ->password()
                    ->revealable()
                    ->required(fn(string $operation): bool => $operation === 'create')
                    ->minLength(8)
                    ->maxLength(255)
                    ->dehydrated(
                        fn(?string $state): bool => filled($state)
                    )
                    ->helperText(
                        fn(string $operation): string =>
                        $operation === 'edit'
                            ? 'Laissez vide pour conserver le mot de passe actuel.'
                            : 'Minimum 8 caractères.'
                    ),
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
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('roles.name')
                    ->label('Rôle')
                    ->badge()
                    ->formatStateUsing(
                        fn($state) => match ($state) {
                            'administrateur' => 'Administrateur',
                            'gestionnaire_stock' => 'Gestionnaire de stock',
                            'caissier' => 'Caissier',
                            default => ucfirst(str_replace('_', ' ', $state ?? '')),
                        }
                    )
                    ->color(
                        fn($state) => match ($state) {
                            'administrateur' => 'danger',
                            'gestionnaire_stock' => 'warning',
                            'caissier' => 'info',
                            default => 'gray',
                        }
                    ),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Rôle')
                    ->options([
                        'administrateur' => 'Administrateur',
                        'gestionnaire_stock' => 'Gestionnaire de stock',
                        'caissier' => 'Caissier',
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['value'] ?? null,
                            fn($query, $role) => $query->role($role)
                        );
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
    BulkActionGroup::make([
        DeleteBulkAction::make(),
    ]),
])
            ->defaultSort('created_at', 'desc')
            ->searchPlaceholder('Rechercher un utilisateur...')
            ->emptyStateHeading('Aucun utilisateur')
            ->emptyStateDescription(
                'Commencez par créer un utilisateur.'
            );
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    // public static function canAccess(): bool
    // {
    //     return auth()->check()
    //         && auth()->user()->hasPermissionTo('users.view');
    // }
}
