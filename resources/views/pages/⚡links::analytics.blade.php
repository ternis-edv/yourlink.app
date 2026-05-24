<?php

use App\Models\Link;
use App\Models\LinkClick;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('components.layouts.dashboard')] #[Title('Link Analytics')] class extends Component
{
    public Link $link;

    public function mount(Link $link): void
    {
        if ($link->user_id !== Auth::id()) {
            abort(403);
        }

        $this->link = $link;
    }

    public function getChartData(): array
    {
        // Get clicks for the last 14 days
        $clicks = LinkClick::where('link_id', $this->link->id)
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
        return [
            'totalClicks' => $this->link->clicks()->count(),
            'uniqueClicks' => $this->link->clicks()->distinct('ip_address')->count(),
            'chartData' => $this->getChartData(),
            'topCountries' => $this->link->clicks()
                ->select('country', DB::raw('count(*) as count'))
                ->whereNotNull('country')
                ->groupBy('country')
                ->orderBy('count', 'desc')
                ->take(5)
                ->get(),
        ];
    }
};
?>

<div>
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:truncate sm:text-3xl sm:tracking-tight">{{ __('Analytics for') }} yourlink.app/{{ $link->hash }}</h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 truncate">{{ $link->original_url }}</p>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0">
            <a href="{{ route('links.index') }}" class="inline-flex items-center rounded-md bg-white dark:bg-slate-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-300 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors" wire:navigate>
                {{ __('Back to Links') }}
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-8">
        <div class="bg-white dark:bg-slate-900 px-4 py-5 shadow-sm ring-1 ring-gray-200 dark:ring-slate-800 rounded-lg overflow-hidden">
            <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Total Clicks') }}</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-yourlink-600">{{ $totalClicks }}</dd>
        </div>
        <div class="bg-white dark:bg-slate-900 px-4 py-5 shadow-sm ring-1 ring-gray-200 dark:ring-slate-800 rounded-lg overflow-hidden">
            <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Unique Visitors') }}</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-yourlink-600">{{ $uniqueClicks }}</dd>
        </div>
        <div class="bg-white dark:bg-slate-900 px-4 py-5 shadow-sm ring-1 ring-gray-200 dark:ring-slate-800 rounded-lg overflow-hidden">
            <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Conversion Rate') }}</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-yourlink-600">100%</dd>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <!-- Main Chart -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 shadow-sm ring-1 ring-gray-200 dark:ring-slate-800 rounded-lg p-6">
            <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white mb-4">{{ __('Clicks Over Time (Last 14 Days)') }}</h3>
            <div class="h-64" 
                 x-data="{
                    init() {
                        const ctx = document.getElementById('clicksChart').getContext('2d');
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
                <canvas id="clicksChart"></canvas>
            </div>
        </div>

        <!-- Country Distribution -->
        <div class="bg-white dark:bg-slate-900 shadow-sm ring-1 ring-gray-200 dark:ring-slate-800 rounded-lg p-6">
            <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white mb-4">{{ __('Top Locations') }}</h3>
            <ul role="list" class="space-y-4">
                @forelse($topCountries as $item)
                    <li class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $item->country ?: __('Unknown') }}</span>
                        <div class="flex items-center gap-4 flex-grow mx-4">
                            <div class="h-2 w-full bg-gray-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full bg-yourlink-500" style="width: {{ ($item->count / $totalClicks) * 100 }}%"></div>
                            </div>
                        </div>
                        <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $item->count }}</span>
                    </li>
                @empty
                    <li class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">
                        {{ __('No location data available yet.') }}
                    </li>
                @endforelse
            </ul>
        </div>
    </div>

    <!-- Script for ChartJS -->
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endpush
</div>
