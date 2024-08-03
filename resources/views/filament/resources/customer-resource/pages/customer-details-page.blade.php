<x-filament::page>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <h2 class="text-xl font-bold">Customer Details</h2>
            <ul class="mt-2">
                <li><strong>Name:</strong> {{ $customer->first_name }} {{ $customer->last_name }}</li>
                <li><strong>Email:</strong> {{ $customer->email }}</li>
                <li><strong>Total Orders:</strong> {{ $customer->orders()->count() }}</li>
                <li><strong>Total Spent:</strong> ${{ number_format($customer->orders()->sum('total'), 2) }}</li>
            </ul>
        </div>
        <div>
            <h2 class="text-xl font-bold">Order Statistics</h2>
            <x-filament::card>
                <p><strong>Total Orders:</strong> {{ $customer->orders()->count() }}</p>
                <p><strong>Total Spent:</strong> ${{ number_format($customer->orders()->sum('total'), 2) }}</p>
                <p><strong>Average Order Value:</strong> ${{ number_format($customer->orders()->average('total'), 2) }}</p>
            </x-filament::card>
        </div>
    </div>
</x-filament::page>
