<?php

namespace App\Filament\Resources\ProductReturns\Pages;

use App\Enums\ReturnStatus;
use App\Filament\Resources\ProductReturns\ProductReturnResource;
use App\Services\ReturnService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use RuntimeException;

class EditProductReturn extends EditRecord
{
    protected static string $resource =
        ProductReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Action::make('complete')
                ->label('Traiter le retour')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading(
                    'Traiter ce retour ?'
                )
                ->modalDescription(
                    'Cette opération sera définitive. '
                    . 'Si le produit est marqué comme revendable, '
                    . 'il sera ajouté au stock.'
                )
                ->visible(
                    fn () =>
                        $this->record->status
                        === ReturnStatus::PENDING
                )
                ->action(function () {

                    try {

                        app(ReturnService::class)
                            ->complete($this->record);

                        Notification::make()
                            ->title('Retour traité')
                            ->body(
                                'Le retour a été enregistré '
                                . 'et le stock a été mis à jour.'
                            )
                            ->success()
                            ->send();

                        $this->record->refresh();

                        $this->fillForm();

                    } catch (RuntimeException $exception) {

                        Notification::make()
                            ->title(
                                'Impossible de traiter le retour'
                            )
                            ->body(
                                $exception->getMessage()
                            )
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}