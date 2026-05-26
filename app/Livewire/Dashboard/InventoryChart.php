<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Services\StatisticsService;
use Illuminate\View\View;
use Livewire\Component;

class InventoryChart extends Component
{
    /**
     * @var array{normal:int,bajo_minimo:int,sin_stock:int}
     */
    public array $distribution = [
        'normal' => 0,
        'bajo_minimo' => 0,
        'sin_stock' => 0,
    ];

    public function mount(StatisticsService $statisticsService): void
    {
        $this->distribution = $statisticsService->getInventoryStatusDistribution();
    }

    public function render(): View
    {
        return view('livewire.dashboard.inventory-chart');
    }
}
