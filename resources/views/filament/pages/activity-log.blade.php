<x-filament-panels::page>
    <div class="mb-6 grid gap-4 md:grid-cols-3">
        <x-filament::card>
            <div class="text-sm font-medium text-gray-500">Last Hour</div>
            <div class="text-3xl font-semibold">{{ $overview['last_hour']['total'] }}</div>
            <div class="text-sm text-gray-500 mt-2">
                Products: <span class="font-medium">{{ $overview['last_hour']['products'] }}</span>
            </div>
        </x-filament::card>
        
        <x-filament::card>
            <div class="text-sm font-medium text-gray-500">Last 24 Hours</div>
            <div class="text-3xl font-semibold">{{ $overview['last_day']['total'] }}</div>
            <div class="text-sm text-gray-500 mt-2">
                Products: <span class="font-medium">{{ $overview['last_day']['products'] }}</span>
            </div>
        </x-filament::card>
        
        <x-filament::card>
            <div class="text-sm font-medium text-gray-500">Last Month</div>
            <div class="text-3xl font-semibold">{{ $overview['last_month']['total'] }}</div>
            <div class="text-sm text-gray-500 mt-2">
                Products: <span class="font-medium">{{ $overview['last_month']['products'] }}</span>
            </div>
        </x-filament::card>
    </div>

    {{ $this->table }}
</x-filament-panels::page>