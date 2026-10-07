<?php

// namespace App\Filament\Resources\Sales\Pages;

// use App\Filament\Resources\Sales\SaleResource;
// use Filament\Actions\DeleteAction;
// use Filament\Actions\ViewAction;
// use Filament\Resources\Pages\EditRecord;

// class EditSale extends EditRecord
// {
//     protected static string $resource = SaleResource::class;

//     protected function getHeaderActions(): array
//     {
//         return [
//             ViewAction::make(),
//             DeleteAction::make(),
//         ];
//     }
// }




namespace App\Filament\Resources\Sales\Pages;

use App\Enums\SaleStatus;
use App\Filament\Resources\Sales\SaleResource;
use App\Services\SaleService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use RuntimeException;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Action::make('complete')
                ->label('Terminer la vente')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Terminer cette vente ?')
                ->modalDescription(
                    'Le stock sera automatiquement diminué selon les articles vendus.'
                )
                ->visible(
                    fn () =>
                        $this->record->status === SaleStatus::DRAFT
                )
                ->action(function () {

                    try {

                        app(SaleService::class)
                            ->complete($this->record);

                        Notification::make()
                            ->title('Vente terminée')
                            ->body(
                                'La vente a été enregistrée et le stock a été mis à jour.'
                            )
                            ->success()
                            ->send();

                        $this->record->refresh();

                        $this->fillForm();

                    } catch (RuntimeException $exception) {

                        Notification::make()
                            ->title('Vente impossible')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}