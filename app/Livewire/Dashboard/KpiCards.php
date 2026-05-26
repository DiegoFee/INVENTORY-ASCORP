<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Services\DashboardService;
use Illuminate\View\View;
use Livewire\Component;

class KpiCards extends Component
{
    /**
     * @var array<string, int|float>
     */
    public array $kpis = [];

    public function mount(DashboardService $dashboardService): void
    {
        $this->kpis = $dashboardService->getGeneralKPIs();
    }

    public function refreshKpis(DashboardService $dashboardService): void
    {
        $this->kpis = $dashboardService->getGeneralKPIs();
    }

    public function render(): View
    {
        return view('livewire.dashboard.kpi-cards');
    }
}
