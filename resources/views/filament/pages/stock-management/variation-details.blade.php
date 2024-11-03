<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($getRecord()->variations as $variation)
            <div class="p-4 bg-gray-50 rounded-lg">
                <div class="font-medium text-lg mb-2">{{ $variation->name }}</div>
                
                <div class="grid grid-cols-2 gap-2">
                    {{-- @foreach($variation->channelStock as $stock)
                        <div @class([
                            'px-3 py-2 rounded-md',
                            'bg-danger-100 text-danger-700' => $stock->quantity <= 0,
                            'bg-warning-100 text-warning-700' => $stock->quantity > 0 && $stock->quantity < 5,
                            'bg-success-100 text-success-700' => $stock->quantity >= 5,
                        ])>
                            <div class="text-sm font-medium">{{ $stock->channel }}</div>
                            <div class="text-lg">{{ $stock->quantity }}</div>
                        </div>
                    @endforeach --}}
                </div>

                <div class="mt-2 text-sm text-gray-500">
                    Last updated: {{ $variation->updated_at->diffForHumans() }}
                </div>
            </div>
        @endforeach
    </div>
</div>