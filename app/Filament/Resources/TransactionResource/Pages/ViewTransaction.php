<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Models\Transaction;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;

class ViewTransaction extends ViewRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getActions(): array
    {
        return [
            Actions\Action::make('markAsCompleted')
                ->label('Mark as Completed')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->color('success')
                ->visible(fn($record) => $record->status === 'processing')
                ->action(function ($record) {
                    $record->update(['status' => 'completed']);

                    Notification::make()
                        ->title('Status Updated')
                        ->body('The status has been marked as completed.')
                        ->success()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('index'));
                }),
        ];
    }
}