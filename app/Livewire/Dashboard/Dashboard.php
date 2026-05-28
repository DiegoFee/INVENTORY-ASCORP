<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Enums\Permission;
use App\Services\CxcDashboardService;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Gate;
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

        $this->indicadoresCxc = Gate::allows(Permission::CxcView->value)
            ? $cxcService->getIndicadores()
            : [];
    }

    public function render(): View
    {
        return view('livewire.dashboard.dashboard');
    }
}
