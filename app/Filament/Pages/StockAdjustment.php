<?php

namespace App\Filament\Pages;

use App\Enums\StockMovementType;
use App\Models\ProductVariant;
use App\Services\StockService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class StockAdjustment extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static ?string $navigationLabel = 'Gestion du stock';

    protected static ?string $title = 'Gestion du stock';

    protected static string|\UnitEnum|null $navigationGroup = 'Stock';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected string $view = 'filament.pages.stock-adjustment';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_variant_id')
                    ->label('Produit / Variante')
                    ->options(
                        ProductVariant::query()
                            ->with(['product', 'size', 'color'])
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
                                    $variant->id => $label,
                                ];
                            })
                            ->toArray()
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('type')
                    ->label('Type d’opération')
                    ->options([
                        StockMovementType::RETURN->value => 'Retour client',
                        StockMovementType::ADJUSTMENT_IN->value => 'Correction positive',
                        StockMovementType::ADJUSTMENT_OUT->value => 'Correction négative',
                        StockMovementType::DAMAGE->value => 'Casse / Perte',
                    ])
                    ->required(),

                TextInput::make('quantity')
                    ->label('Quantité')
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->required(),

                Textarea::make('reason')
                    ->label('Motif / Commentaire')
                    ->rows(4)
                    ->maxLength(1000),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $variant = ProductVariant::findOrFail(
            $data['product_variant_id']
        );

        $type = StockMovementType::from($data['type']);

        $service = app(StockService::class);

        try {

            if ($type->isIncoming()) {

                $service->add(
                    variant: $variant,
                    quantity: (int) $data['quantity'],
                    type: $type,
                    user: Auth::user(),
                    reason: $data['reason'] ?? null,
                );

            } else {

                $service->remove(
                    variant: $variant,
                    quantity: (int) $data['quantity'],
                    type: $type,
                    user: Auth::user(),
                    reason: $data['reason'] ?? null,
                );
            }

            Notification::make()
                ->title('Stock mis à jour')
                ->body('Le mouvement de stock a été enregistré avec succès.')
                ->success()
                ->send();

            $this->form->fill();

        } catch (RuntimeException $exception) {

            Notification::make()
                ->title('Opération impossible')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }



public static function canAccess(): bool
{
    return auth()->user()?->can('stock.adjust') ?? false;
}
}