<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Product extends BaseModel
{    

    protected $fillable = ['sku', 'name', 'price', 'stock_quantity', 'channel_id'];

    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function relatedProductsBySku()
    {
        return $this->hasMany(Product::class, 'sku', 'sku')
            ->where('channel_id', '!=', $this->channel_id);
    }
    

    // Relationship with Order through pivot table
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class)
                    ->withPivot('quantity');  // assuming pivot table has quantity column
    }

    // Relationship with Channel
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }
    
}

