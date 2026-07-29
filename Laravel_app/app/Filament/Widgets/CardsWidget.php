<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Storage;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CardsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('Total number of users in the system.')
                ->descriptionIcon(Heroicon::OutlinedUsers)
                ->chart([10, 20, 15, 30, 25, 40])
                ->color('success'),
            Stat::make('Categories', Category::count())
                ->description('Total number of categories.')
                ->descriptionIcon(Heroicon::OutlinedTag)
                ->chart([2, 4, 3, 5, 4, 6])
                ->color('info'),
            Stat::make('Products', Product::count())
                ->description('Total number of products.')
                ->descriptionIcon(Heroicon::OutlinedCube)
                ->chart([8, 12, 10, 15, 12, 18])
                ->color('warning'),
            Stat::make('Storages', Storage::count())
                ->description('Total number of storages.')
                ->descriptionIcon(Heroicon::OutlinedArchiveBox)
                ->chart([3, 5, 4, 6, 5, 7])
                ->color('danger'),
        ];
    }
}
