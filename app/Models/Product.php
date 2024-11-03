<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Product extends BaseModel
{    
    use LogsActivity;

    protected $with = ['channel'];
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
    
    public function getRelatedProductsByChannel()
    {
        return $this->relatedProductsBySku()
            ->with('channel')
            ->get()
            ->groupBy('channel.name');
    }

    public function getGroupedRelatedProducts()
    {
        return $this->relatedProductsBySku()
            ->with('channel')
            ->get()
            ->groupBy('channel.name')
            ->map(function ($products) {
                return $products->map(function ($product) {
                    return [
                        'sku' => $product->sku,
                        'name' => $product->name,
                        'price' => $product->price,
                        'channel' => $product->channel,
                    ];
                });
            });
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('products')
            ->setDescriptionForEvent(fn(string $eventName) => "Product has been {$eventName}");
    }
    
}

