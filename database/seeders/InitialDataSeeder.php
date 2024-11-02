<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Order;
use App\Models\Customer;

class InitialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Users
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create Products
        $product1 = Product::create([
            'sku' => 'PROD001',
            'name' => 'Product 1',
            'price' => 100,
            'stock_quantity' => 50,
            'channel_id' => 1,
        ]);

        $product2 = Product::create([
            'sku' => 'PROD002',
            'name' => 'Product 2',
            'price' => 200,
            'stock_quantity' => 30,
            'channel_id' => 1,
        ]);

        // Create Product Variations
        ProductVariation::create([
            'product_id' => $product1->id,
            'sku' => 'PROD001-VAR1',
            'name' => 'Product 1 Variation 1',
            'price' => 110,
            'stock_quantity' => 20,
        ]);

        ProductVariation::create([
            'product_id' => $product2->id,
            'sku' => 'PROD002-VAR1',
            'name' => 'Product 2 Variation 1',
            'price' => 210,
            'stock_quantity' => 15,
        ]);

        // Create Customers
        $customer1 = Customer::create([
            'woocommerce_id' => 1,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'channel_id' => 1,
            'channel' => 'WooCommerce',
        ]);

        $customer2 = Customer::create([
            'woocommerce_id' => 2,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@example.com',
            'channel_id' => 1,
            'channel' => 'WooCommerce',
        ]);

        // Create Orders
        $order1 = Order::create([
            'woocommerce_id' => 1,
            'channel_id' => 1,
            'channel' => 'WooCommerce',
            'total' => 150,
            'status' => 'completed',
            'customer_id' => $customer1->id,
            'billing_first_name' => 'John',
            'billing_last_name' => 'Doe',
            'billing_address_1' => '123 Main St',
            'billing_address_2' => 'Apt 4B',
            'billing_city' => 'New York',
            'billing_state' => 'NY',
            'billing_postcode' => '10001',
            'billing_country' => 'USA',
            'billing_email' => 'john.doe@example.com',
            'billing_phone' => '123-456-7890',
            'original_order_date' => now(),
        ]);

        $order2 = Order::create([
            'woocommerce_id' => 2,
            'channel_id' => 1,
            'channel' => 'WooCommerce',
            'total' => 250,
            'status' => 'completed',
            'customer_id' => $customer2->id,
            'billing_first_name' => 'Jane',
            'billing_last_name' => 'Doe',
            'billing_address_1' => '456 Elm St',
            'billing_address_2' => 'Apt 2A',
            'billing_city' => 'Los Angeles',
            'billing_state' => 'CA',
            'billing_postcode' => '90001',
            'billing_country' => 'USA',
            'billing_email' => 'jane.doe@example.com',
            'billing_phone' => '987-654-3210',
            'original_order_date' => now(),
        ]);

        // Attach Products to Orders
        $order1->products()->attach($product1->id, ['quantity' => 2]);
        $order2->products()->attach($product2->id, ['quantity' => 1]);
    }
}
