<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

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
                ->badge(Product::withTrashed()->count()),

            'active' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutTrashed())
                ->badge(Product::withoutTrashed()->count())
                ->badgeColor('success'),

            'Featured' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('is_featured', true)->withoutTrashed())
                ->badge(Product::where('is_featured', true)->withoutTrashed()->count())
                ->badgeColor('info'),

            'deleted' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->onlyTrashed())
                ->badge(Product::onlyTrashed()->count())
                ->badgeColor('danger'),
        ];
    }
    public function getDefaultActiveTab(): string|int|null
    {
        return 'active';
    }
}
