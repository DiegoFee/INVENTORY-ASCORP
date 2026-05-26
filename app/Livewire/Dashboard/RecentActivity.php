<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Services\DashboardService;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class RecentActivity extends Component
{
    /**
     * @var Collection<int, array{usuario:string,accion:string,fecha:string}>
     */
    public Collection $activities;

    public function mount(DashboardService $dashboardService): void
    {
        $this->activities = $dashboardService->getRecentActivities();
    }

    public function render(): View
    {
        return view('livewire.dashboard.recent-activity');
    }
}
