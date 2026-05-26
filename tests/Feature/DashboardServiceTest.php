<?php

use App\Services\DashboardService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

test('dashboard service returns expected data structure', function () {
    Cache::forget('dashboard_stats');

    $data = app(DashboardService::class)->getDashboardData();

    expect($data)->toBeArray()
        ->toHaveKeys([
            'kpis',
            'recent_activities',
            'quick_actions',
            'pending_tasks',
            'sales_chart',
            'inventory_chart',
            'alerts',
        ])
        ->and($data['kpis'])->toBeArray()
        ->and($data['recent_activities'])->toBeInstanceOf(Collection::class)
        ->and($data['quick_actions'])->toBeArray()
        ->and($data['pending_tasks'])->toBeArray()
        ->and($data['sales_chart'])->toBeArray()
        ->and($data['inventory_chart'])->toBeArray()
        ->and($data['alerts'])->toBeInstanceOf(Collection::class);
});

test('dashboard service stores dashboard_stats cache key', function () {
    Cache::forget('dashboard_stats');

    app(DashboardService::class)->getDashboardData();

    expect(Cache::has('dashboard_stats'))->toBeTrue();
});
