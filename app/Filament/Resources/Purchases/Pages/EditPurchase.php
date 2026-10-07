<?php

// namespace App\Filament\Resources\Purchases\Pages;

// use App\Filament\Resources\Purchases\PurchaseResource;
// use Filament\Actions\DeleteAction;
// use Filament\Actions\ViewAction;
// use Filament\Resources\Pages\EditRecord;

// class EditPurchase extends EditRecord
// {
//     protected static string $resource = PurchaseResource::class;

//     protected function getHeaderActions(): array
//     {
//         return [
//             ViewAction::make(),
//             DeleteAction::make(),
//         ];
//     }
// }





namespace App\Filament\Resources\Purchases\Pages;

use App\Enums\PurchaseStatus;
use App\Filament\Resources\Purchases\PurchaseResource;
use App\Services\PurchaseService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use RuntimeException;

class EditPurchase extends EditRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('receive')
                ->label('Réceptionner')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Réceptionner cet achat ?')
                ->modalDescription(
                    'Cette opération augmentera automatiquement le stock des produits concernés.'
                )
                ->visible(
                    fn () => $this->record->status === PurchaseStatus::DRAFT
                )
                ->action(function () {

                    try {

                        app(PurchaseService::class)
                            ->receive($this->record);

                        Notification::make()
                            ->title('Achat réceptionné')
                            ->body(
                                'Le stock a été mis à jour avec succès.'
                            )
                            ->success()
                            ->send();

                        $this->refreshFormData([
                            'status',
                            'subtotal',
                            'total',
                        ]);

                    } catch (RuntimeException $exception) {

                        Notification::make()
                            ->title('Réception impossible')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}