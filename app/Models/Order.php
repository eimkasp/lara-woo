<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    protected $fillable = ['total', 'status', 'customer_id', 'channel_id'];

    // Relationship with Customer
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    // Relationship with Product through pivot table
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
                    ->withPivot('quantity');  // assuming pivot table has quantity column
    }

    // Relationship with Channel
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }
}

