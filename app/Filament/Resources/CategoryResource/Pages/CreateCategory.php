<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;
    protected static bool $isModal = true;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make('Transaction created!!')
            ->title('Category created!!')
            ->body('You have successfully created a new category.')
            ->success()
            ->color('success')
            ->icon('heroicon-o-check-circle')
            ;
    }
    
}
