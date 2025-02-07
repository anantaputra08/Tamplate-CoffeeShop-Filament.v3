<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->withTrashed())
                ->badge(User::withTrashed()->count()),
    
            'active' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutTrashed())
                ->badge(User::withoutTrashed()->count())
                ->badgeColor('success'),
    
            'deleted' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->onlyTrashed())
                ->badge(User::onlyTrashed()->count())
                ->badgeColor('danger'),
        ];
    }
}
