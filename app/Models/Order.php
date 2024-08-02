<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'woocommerce_id',
        'total',
        'status',
        'channel_id',
        'channel',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_product')
                    ->withPivot('quantity')
                    ->withTimestamps();
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

