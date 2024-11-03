<x-filament-panels::page>
    <div class="space-y-6">
        @if($this->getHeaderWidgets())
            <div class="filament-widgets-container">
                {{-- {{ $this->getHeaderWidgetsGrid() }} --}}
            </div>
        @endif

        {{ $this->table }}
    </div>
</x-filament-panels::page>