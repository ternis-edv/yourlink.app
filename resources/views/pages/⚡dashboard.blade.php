<?php

use App\Models\Link;
use App\Models\LinkClick;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('components.layouts.dashboard')] #[Title('Dashboard')] class extends Component
{
    // Create Link Form State
    public string $original_url = '';
    public ?string $custom_alias = null;
    public ?string $password = null;
    public ?string $expires_at = null;
    public ?int $click_limit = null;
    public int $link_length = 7;
    public bool $show_advanced = false;

    public function mount(): void
    {
        $this->link_length = Auth::user()->settings['default_link_length'] ?? 7;
    }

    public function toggleAdvanced(): void
    {
        $this->show_advanced = !$this->show_advanced;
    }

    public function createLink(): void
    {
        $this->validate([
            'original_url' => ['required', 'url', 'max:2048'],
            'custom_alias' => ['nullable', 'string', 'alpha_dash', 'max:50', 'unique:links,hash'],
            'password' => ['nullable', 'string', 'min:4', 'max:255'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'click_limit' => ['nullable', 'integer', 'min:1'],
            'link_length' => ['required', 'integer', 'min:5', 'max:20'],
        ]);

        if ($this->custom_alias) {
            $hash = $this->custom_alias;
        } else {
            $hash = Str::random($this->link_length);
            while (Link::where('hash', $hash)->exists()) {
                $hash = Str::random($this->link_length);
            }
        }

        $settings = [];
        if ($this->password) {
            $settings['password'] = Hash::make($this->password);
        }
        if ($this->click_limit) {
            $settings['click_limit'] = $this->click_limit;
        }

        Link::create([
            'user_id' => Auth::id(),
            'original_url' => $this->original_url,
            'hash' => $hash,
            'expires_at' => $this->expires_at,
            'settings' => empty($settings) ? null : $settings,
        ]);

        $this->reset(['original_url', 'custom_alias', 'password', 'expires_at', 'click_limit', 'show_advanced']);
        $this->link_length = Auth::user()->settings['default_link_length'] ?? 7;
        
        session()->flash('message', __('Link created successfully.'));
    }

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
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ __('Overview') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Welcome back, :name.', ['name' => auth()->user()->name]) }}</p>
        </div>
    </div>

    <!-- Create Link Form -->
    <div class="bg-white dark:bg-slate-900 shadow-sm ring-1 ring-gray-200 dark:ring-slate-800 rounded-lg p-6 mb-8">
        <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white mb-4">{{ __('Quick Shorten') }}</h3>
        
        @if (session()->has('message'))
            <div class="mb-4 rounded-md bg-green-50 dark:bg-green-900/20 p-4 ring-1 ring-inset ring-green-700/10">
                <p class="text-sm font-medium text-green-700 dark:text-green-400">{{ session('message') }}</p>
            </div>
        @endif

        <form wire:submit="createLink" class="space-y-4">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-grow">
                    <input wire:model="original_url" type="url" placeholder="{{ __('Paste your long URL here') }}" required class="block w-full rounded-md border-0 py-2.5 text-gray-900 dark:text-white bg-white dark:bg-slate-800 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-yourlink-600 sm:text-sm sm:leading-6">
                    @error('original_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="rounded-md bg-yourlink-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-yourlink-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-yourlink-600 transition-colors whitespace-nowrap">
                    {{ __('Create Link') }}
                </button>
            </div>

            <div>
                <button type="button" wire:click="toggleAdvanced" class="flex items-center gap-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                    <svg class="h-4 w-4 transform transition-transform {{ $show_advanced ? 'rotate-180' : '' }}" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                    {{ __('Advanced Options') }}
                </button>

                @if($show_advanced)
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mt-4 p-4 bg-gray-50 dark:bg-slate-800/50 rounded-lg animate-in slide-in-from-top-2 fade-in duration-200">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Custom Alias') }}</label>
                            <input wire:model="custom_alias" type="text" placeholder="{{ __('e.g. my-link') }}" class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-white bg-white dark:bg-slate-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 sm:text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Hash Length') }}</label>
                            <select wire:model="link_length" class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-white bg-white dark:bg-slate-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 sm:text-xs">
                                @for($i = 5; $i <= 12; $i++)
                                    <option value="{{ $i }}">{{ $i }} {{ __('chars') }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Click Limit') }}</label>
                            <input wire:model="click_limit" type="number" min="1" class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-white bg-white dark:bg-slate-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 sm:text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Password') }}</label>
                            <input wire:model="password" type="password" class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-white bg-white dark:bg-slate-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 sm:text-xs">
                        </div>
                    </div>
                @endif
            </div>
        </form>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <div class="overflow-hidden rounded-lg bg-white dark:bg-slate-900 px-4 py-5 shadow-sm ring-1 ring-gray-200 dark:ring-slate-800 sm:p-6 transition-all hover:shadow-md">
            <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Total Links') }}</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-yourlink-600">{{ $totalLinks }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-white dark:bg-slate-900 px-4 py-5 shadow-sm ring-1 ring-gray-200 dark:ring-slate-800 sm:p-6 transition-all hover:shadow-md">
            <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Total Clicks') }}</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-yourlink-600">{{ $totalClicks }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-white dark:bg-slate-900 px-4 py-5 shadow-sm ring-1 ring-gray-200 dark:ring-slate-800 sm:p-6 transition-all hover:shadow-md">
            <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Avg. Clicks/Link') }}</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-yourlink-600">{{ $totalLinks > 0 ? number_format($totalClicks / $totalLinks, 1) : 0 }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-white dark:bg-slate-900 px-4 py-5 shadow-sm ring-1 ring-gray-200 dark:ring-slate-800 sm:p-6 transition-all hover:shadow-md">
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
                                <span class="text-sm font-medium text-yourlink-600 truncate">yourlink.app/{{ $link->hash }}</span>
                                <span class="text-xs text-gray-500 truncate">{{ $link->original_url }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center rounded-md bg-yourlink-50 dark:bg-yourlink-900/20 px-2 py-1 text-xs font-medium text-yourlink-700 dark:text-yourlink-400 ring-1 ring-inset ring-yourlink-700/10">{{ $link->clicks_count }} {{ __('clicks') }}</span>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
            <div class="bg-gray-50 dark:bg-slate-900/50 px-4 py-3 sm:px-6 text-right">
                <a href="{{ route('links.index') }}" class="text-sm font-medium text-yourlink-600 hover:text-yourlink-500" wire:navigate>{{ __('View all') }} &rarr;</a>
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
                                <span class="text-sm font-medium text-yourlink-600 truncate">yourlink.app/{{ $link->hash }}</span>
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
