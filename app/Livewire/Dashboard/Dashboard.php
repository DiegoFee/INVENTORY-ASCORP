<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Services\DashboardService;
use App\Services\CxcDashboardService;
use Illuminate\View\View;
use Livewire\Component;

class Dashboard extends Component
{
    /**
     * @var list<array{label:string,route:string,icon:string}>
     */
    public array $quickActions = [];

    /**
     * @var list<array{label:string,count:int,route:string}>
     */
    public array $pendingTasks = [];

    /**
     * @var list<array{tipo:string,mensaje:string,severidad:int}>
     */
    public array $alerts = [];

    /**
     * @var array<string,mixed> Indicadores financieros de CxC
     */
    public array $indicadoresCxc = [];

    public function mount(DashboardService $dashboardService, CxcDashboardService $cxcService): void
    {
        $data = $dashboardService->getDashboardData();
        $this->quickActions = $data['quick_actions'] ?? [];
        $this->pendingTasks = $data['pending_tasks'] ?? [];
        $this->alerts = is_array($data['alerts']) ? $data['alerts'] : ($data['alerts']?->all() ?? []);

        // Obtener indicadores de Cuentas por Cobrar
        $this->indicadoresCxc = $cxcService->getIndicadores();
    }

    public function render(): View
    {
        return view('livewire.dashboard.dashboard');
    }
}