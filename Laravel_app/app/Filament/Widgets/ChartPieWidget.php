<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Category;

class ChartPieWidget extends ChartWidget
{
    protected ?string $heading = 'Categories Pie Widget';
    // protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $categories = Category::withCount('products')->take(10)->get();


        return [
            'datasets' => [
                [
                    'data' => $categories->pluck('products_count')->toArray(),
                    'backgroundColor' => [
                        '#4ade80',
                        '#f87171',
                        '#60a5fa',
                        '#fbbf24',
                        '#a78bfa',
                        '#f472b6',
                        '#34d399',
                        '#fcd34d',
                        '#3b82f6',
                        '#8b5cf6',
                    ],
                ],
            ],
            'labels' => $categories->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                ],
                'title' => [
                    'display' => true,
                    'text' => 'Categories Pie Chart',
                ],
            ],
        ];
    }
}
