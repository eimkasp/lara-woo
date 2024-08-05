<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'woocommerce_id',
        'channel_id',
        'channel',
        'total',
        'status',
        'customer_id',
        'billing_first_name',
        'billing_last_name',
        'billing_address_1',
        'billing_address_2',
        'billing_city',
        'billing_state',
        'billing_postcode',
        'billing_country',
        'billing_email',
        'billing_phone',
        'original_order_date',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('quantity');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }
}

