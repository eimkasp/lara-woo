<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockChange extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'quantity_change',
        'source'
    ];

    protected $casts = [
        'quantity_change' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($stockChange) {
            $product = $stockChange->product;
            $product->stock_quantity += $stockChange->quantity_change;
            $product->save();
        });
    }
}
