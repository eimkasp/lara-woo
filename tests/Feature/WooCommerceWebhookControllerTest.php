<?php

use App\Models\Product;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

it('can handle product webhook and update stock quantity', function () {
    $product = Product::factory()->create([
        'sku' => 'test-sku',
        'stock_quantity' => 10,
    ]);

    $productData = [
        'sku' => 'test-sku',
        'name' => 'Test Product',
        'price' => 100,
        'stock_quantity' => 20,
        'channel_id' => 1,
    ];

    postJson('/api/webhook/product', ['data' => $productData])
        ->assertStatus(200)
        ->assertJson(['status' => 'success']);

    $product->refresh();

    expect($product->stock_quantity)->toBe(20);
});

it('can handle customer webhook and update customer data', function () {
    $customer = Customer::factory()->create([
        'email' => 'test@example.com',
        'first_name' => 'Old',
        'last_name' => 'Name',
    ]);

    $customerData = [
        'email' => 'test@example.com',
        'first_name' => 'New',
        'last_name' => 'Name',
        'channel_id' => 1,
    ];

    postJson('/api/webhook/customer', ['data' => $customerData])
        ->assertStatus(200)
        ->assertJson(['status' => 'success']);

    $customer->refresh();

    expect($customer->first_name)->toBe('New');
});

it('can handle order webhook and update order data', function () {
    $customer = Customer::factory()->create([
        'email' => 'test@example.com',
    ]);

    $orderData = [
        'id' => 1,
        'total' => 100,
        'status' => 'completed',
        'customer_id' => $customer->id,
        'channel_id' => 1,
        'billing' => [
            'first_name' => 'Test',
            'last_name' => 'User',
            'address_1' => '123 Test St',
            'address_2' => '',
            'city' => 'Test City',
            'state' => 'TS',
            'postcode' => '12345',
            'country' => 'Test Country',
            'email' => 'test@example.com',
            'phone' => '1234567890',
        ],
        'date_created' => now()->toDateTimeString(),
    ];

    postJson('/api/webhook/order', ['data' => $orderData])
        ->assertStatus(200)
        ->assertJson(['status' => 'success']);

    $order = Order::where('woocommerce_id', 1)->first();

    expect($order)->not->toBeNull();
    expect($order->total)->toBe(100);
    expect($order->status)->toBe('completed');
});
