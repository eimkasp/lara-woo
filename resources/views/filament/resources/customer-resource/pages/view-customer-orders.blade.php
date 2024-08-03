<table class="min-w-full bg-white">
    <thead>
        <tr>
            <th class="py-2">Order ID</th>
            <th class="py-2">Date</th>
            <th class="py-2">Status</th>
            <th class="py-2">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($orders as $order)
            <tr>
                <td class="py-2">{{ $order->id }}</td>
                <td class="py-2">{{ $order->created_at->format('Y-m-d') }}</td>
                <td class="py-2">{{ ucfirst($order->status) }}</td>
                <td class="py-2">${{ number_format($order->total, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
