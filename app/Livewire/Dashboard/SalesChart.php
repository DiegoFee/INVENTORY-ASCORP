<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Services\StatisticsService;
use Illuminate\View\View;
use Livewire\Component;

class SalesChart extends Component
{
    /**
     * @var list<array{mes:string,total:float}>
     */
    public array $series = [];

    public function mount(StatisticsService $statisticsService): void
    {
        $this->series = $statisticsService->getSalesByLast12Months();
    }

    public function render(): View
    {
        return view('livewire.dashboard.sales-chart');
    }
}
