<div class="space-y-4">
    @php
        $relatedProducts = collect([$getRecord()])->merge($getRecord()->relatedProductsBySku);
    @endphp
    
    <div class="grid grid-cols-1 gap-4">
        @foreach($relatedProducts as $channelProduct)
            <div class="p-4 bg-gray-50 rounded-lg">
                <div class="font-medium mb-2 flex items-center justify-between">
                    {{ $channelProduct->channel_id }}: {{ $channelProduct->name }} 
                    Stock:
                    {{ $channelProduct->stock_quantity }}
                    {{-- <span>{{ $channelProduct->channel->name }}</span>
                    <span class="text-sm text-gray-500">
                        Updated: {{ $channelProduct->updated_at->diffForHumans() }}
                    </span> --}}
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    @foreach($channelProduct->variations as $variation)
                        <div @class([
                            'p-3 rounded-lg',
                            'bg-danger-100' => $variation->stock_quantity <= 0,
                            'bg-warning-100' => $variation->stock_quantity > 0 && $variation->stock_quantity < 5,
                            'bg-success-100' => $variation->stock_quantity >= 5,
                        ])>
                            <div class="text-sm font-medium">{{ $variation->name }}</div>
                            <div class="text-lg font-bold mt-1">
                                {{ $variation->stock_quantity }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>