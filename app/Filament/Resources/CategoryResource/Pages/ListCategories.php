<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Models\Category;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ListRecords;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

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
                ->badge(Category::withTrashed()->count()),
    
            'active' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutTrashed())
                ->badge(Category::withoutTrashed()->count())
                ->badgeColor('success'),
    
            'deleted' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->onlyTrashed())
                ->badge(Category::onlyTrashed()->count())
                ->badgeColor('danger'),
        ];
    }
}
