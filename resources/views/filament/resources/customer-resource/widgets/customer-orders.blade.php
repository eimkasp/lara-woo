<div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
    @foreach ($orders as $order)
        <a href="{{ route('filament.resources.orders.view', $order->id) }}" class="block p-4 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Order #{{ $order->id }}</h3>
            <p class="mt-1 text-sm text-gray-500">Status: {{ $order->status }}</p>
            <p class="mt-1 text-sm text-gray-500">Total: ${{ number_format($order->total, 2) }}</p>
            <p class="mt-1 text-sm text-gray-500">Placed on: {{ $order->created_at->format('d M, Y') }}</p>
        </a>
    @endforeach
</div>
