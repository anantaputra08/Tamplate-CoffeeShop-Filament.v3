<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Models\Transaction;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

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
                ->badge(Transaction::withTrashed()->count()),

            'active' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutTrashed())
                ->badge(Transaction::withoutTrashed()->count())
                ->badgeColor('success'),

            'completed' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'completed')->withoutTrashed())
                ->badge(Transaction::where('status', 'completed')->withoutTrashed()->count())
                ->badgeColor('success'),

            'processing' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'processing')->withoutTrashed())
                ->badge(Transaction::where('status', 'processing')->withoutTrashed()->count())
                ->badgeColor('success'),

            'pending' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'pending')->withoutTrashed())
                ->badge(Transaction::where('status', 'pending')->withoutTrashed()->count()),

            'deleted' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->onlyTrashed())
                ->badge(Transaction::onlyTrashed()->count())
                ->badgeColor('danger'),
        ];
    }
    public function getDefaultActiveTab(): string|int|null
    {
        return 'active';
    }

    
}
