<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Enums\Permission;
use App\Services\StatisticsService;
use Illuminate\Support\Facades\Gate;
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
        Gate::authorize(Permission::VentasView->value);

        $this->series = $statisticsService->getSalesByLast12Months();
    }

    public function render(): View
    {
        return view('livewire.dashboard.sales-chart');
    }
}
