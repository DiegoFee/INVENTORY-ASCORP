<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Services\DashboardService;
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

    public function mount(DashboardService $dashboardService): void
    {
        $data = $dashboardService->getDashboardData();
        $this->quickActions = $data['quick_actions'];
        $this->pendingTasks = $data['pending_tasks'];
        $this->alerts = $data['alerts']->all();
    }

    public function render(): View
    {
        return view('livewire.dashboard.dashboard');
    }
}
