<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;

class WooCommerceWebhookController extends Controller
{
    public function handleOrder(Request $request)
    {
        // Extract order data from the request
        $orderData = $request->input('data');
        
        // Process the order data
        $this->syncOrder($orderData);

        return response()->json(['status' => 'success']);
    }

    public function handleProduct(Request $request)
    {
        // Extract product data from the request
        $productData = $request->input('data');
        
        // Process the product data
        $this->syncProduct($productData);

        return response()->json(['status' => 'success']);
    }

    public function handleCustomer(Request $request)
    {
        // Extract customer data from the request
        $customerData = $request->input('data');
        
        // Process the customer data
        $this->syncCustomer($customerData);

        return response()->json(['status' => 'success']);
    }

    protected function syncOrder($orderData)
    {
        // Implement logic to sync order data with your database
        $orderModel = Order::updateOrCreate(
            [
                'woocommerce_id' => $orderData['id'],
            ],
            [
                'total' => $orderData['total'],
                'status' => $orderData['status'],
                'customer_id' => $orderData['customer_id'],
                'channel_id' => $orderData['channel_id'],
                'billing_first_name' => $orderData['billing']['first_name'],
                'billing_last_name' => $orderData['billing']['last_name'],
                'billing_address_1' => $orderData['billing']['address_1'],
                'billing_address_2' => $orderData['billing']['address_2'],
                'billing_city' => $orderData['billing']['city'],
                'billing_state' => $orderData['billing']['state'],
                'billing_postcode' => $orderData['billing']['postcode'],
                'billing_country' => $orderData['billing']['country'],
                'billing_email' => $orderData['billing']['email'],
                'billing_phone' => $orderData['billing']['phone'],
                'original_order_date' => $orderData['date_created'],
            ]
        );
    }

    protected function syncProduct($productData)
    {
        // Implement logic to sync product data with your database
        $productModel = Product::updateOrCreate(
            [
                'sku' => $productData['sku'],
            ],
            [
                'name' => $productData['name'],
                'price' => $productData['price'],
                'stock_quantity' => $productData['stock_quantity'],
                'channel_id' => $productData['channel_id'],
            ]
        );
    }

    protected function syncCustomer($customerData)
    {
        // Implement logic to sync customer data with your database
        $customerModel = Customer::updateOrCreate(
            [
                'email' => $customerData['email'],
            ],
            [
                'first_name' => $customerData['first_name'],
                'last_name' => $customerData['last_name'],
                'channel_id' => $customerData['channel_id'],
            ]
        );
    }
}

