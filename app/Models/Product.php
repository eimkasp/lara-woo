<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'woocommerce_id',
        'sku',
        'name',
        'price',
        'stock_quantity',
        'channel_id',
        'channel',
    ];

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_product')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }
}

