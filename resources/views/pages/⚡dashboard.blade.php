<?php

use App\Models\Link;
use App\Models\LinkClick;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('components.layouts.dashboard')] #[Title('Dashboard')] class extends Component
{
    public function getChartData(): array
    {
        $user = Auth::user();
        
        $clicks = LinkClick::whereIn('link_id', $user->links()->pluck('id'))
            ->where('created_at', '>=', now()->subDays(14))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $labels = [];
        $data = [];
        
        $currentDate = now()->subDays(14);
        for ($i = 0; $i <= 14; $i++) {
            $dateString = $currentDate->format('Y-m-d');
            $labels[] = $currentDate->format('M d');
            
            $click = $clicks->firstWhere('date', $dateString);
            $data[] = $click ? $click->count : 0;
            
            $currentDate->addDay();
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    public function with(): array
    {
        $user = Auth::user();
        
        return [
            'totalLinks' => $user->links()->count(),
            'totalClicks' => LinkClick::whereIn('link_id', $user->links()->pluck('id'))->count(),
            'recentLinks' => $user->links()->latest()->take(5)->withCount('clicks')->get(),
            'topLinks' => $user->links()->withCount('clicks')->orderBy('clicks_count', 'desc')->take(5)->get(),
            'chartData' => $this->getChartData(),
        ];
    }
};
?>

<div>
    <div class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ __('Overview') }}</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Welcome back, :name. Here is what is happening with your links.', ['name' => auth()->user()->name]) }}</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <div class="overflow-hidden rounded-lg bg-white dark:bg-slate-900 px-4 py-5 shadow-sm ring-1 ring-gray-200 dark:ring-slate-800 sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Total Links') }}</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-yourlink-600">{{ $totalLinks }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-white dark:bg-slate-900 px-4 py-5 shadow-sm ring-1 ring-gray-200 dark:ring-slate-800 sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Total Clicks') }}</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-yourlink-600">{{ $totalClicks }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-white dark:bg-slate-900 px-4 py-5 shadow-sm ring-1 ring-gray-200 dark:ring-slate-800 sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Avg. Clicks/Link') }}</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-yourlink-600">{{ $totalLinks > 0 ? number_format($totalClicks / $totalLinks, 1) : 0 }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-white dark:bg-slate-900 px-4 py-5 shadow-sm ring-1 ring-gray-200 dark:ring-slate-800 sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Active Domains') }}</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-yourlink-600">{{ auth()->user()->domains()->count() }}</dd>
        </div>
    </div>

    <!-- Main Dashboard Chart -->
    <div class="bg-white dark:bg-slate-900 shadow-sm ring-1 ring-gray-200 dark:ring-slate-800 rounded-lg p-6 mb-8">
        <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white mb-4">{{ __('Total Clicks Over Time (Last 14 Days)') }}</h3>
        <div class="h-64" 
             x-data="{
                init() {
                    const ctx = document.getElementById('dashboardChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: {{ json_encode($chartData['labels']) }},
                            datasets: [{
                                label: 'Clicks',
                                data: {{ json_encode($chartData['data']) }},
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                                x: { grid: { display: false } }
                            }
                        }
                    });
                }
             }"
             wire:ignore
        >
            <canvas id="dashboardChart"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
        <!-- Recent Links -->
        <div class="bg-white dark:bg-slate-900 shadow-sm ring-1 ring-gray-200 dark:ring-slate-800 rounded-lg overflow-hidden">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-slate-800">
                <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">{{ __('Recently Created') }}</h3>
            </div>
            <ul role="list" class="divide-y divide-gray-200 dark:divide-slate-800">
                @foreach($recentLinks as $link)
                    <li class="px-4 py-4 sm:px-6 hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex flex-col truncate">
                                <span class="text-sm font-medium text-blue-600 truncate">yourlink.app/{{ $link->hash }}</span>
                                <span class="text-xs text-gray-500 truncate">{{ $link->original_url }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center rounded-md bg-blue-50 dark:bg-blue-900/20 px-2 py-1 text-xs font-medium text-blue-700 dark:text-blue-400 ring-1 ring-inset ring-blue-700/10">{{ $link->clicks_count }} {{ __('clicks') }}</span>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
            <div class="bg-gray-50 dark:bg-slate-900/50 px-4 py-3 sm:px-6 text-right">
                <a href="{{ route('links.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-500" wire:navigate>{{ __('View all') }} &rarr;</a>
            </div>
        </div>

        <!-- Top Links -->
        <div class="bg-white dark:bg-slate-900 shadow-sm ring-1 ring-gray-200 dark:ring-slate-800 rounded-lg overflow-hidden">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-slate-800">
                <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">{{ __('Top Performing') }}</h3>
            </div>
            <ul role="list" class="divide-y divide-gray-200 dark:divide-slate-800">
                @foreach($topLinks as $link)
                    <li class="px-4 py-4 sm:px-6 hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex flex-col truncate">
                                <span class="text-sm font-medium text-blue-600 truncate">yourlink.app/{{ $link->hash }}</span>
                                <span class="text-xs text-gray-500 truncate">{{ $link->title ?: $link->original_url }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center rounded-md bg-green-50 dark:bg-green-900/20 px-2 py-1 text-xs font-medium text-green-700 dark:text-green-400 ring-1 ring-inset ring-green-700/10">{{ $link->clicks_count }} {{ __('clicks') }}</span>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endpush
</div>
