<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Category;

class ChartBarWidget extends ChartWidget
{
    protected ?string $heading = 'Products for category';

    protected function getData(): array
    {
        $results = Category::withCount('products')
            ->orderBy('products_count', 'desc')
            ->take(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Products',
                    'data' => $results->pluck('products_count')->toArray(),
                    'backgroundColor' => '#4ade80',
                ],
            ],
            'labels' => $results->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
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
                    'text' => 'Top 10 Categories by Product Count',
                ],
            ],
            'scales' => [
                'y' => [
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
