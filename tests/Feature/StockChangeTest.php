use App\Models\Product;
use App\Models\StockChange;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('tracks stock changes', function () {
    $product = Product::factory()->create(['stock_quantity' => 0]);
    
    $stockChange = StockChange::create([
        'product_id' => $product->id,
        'quantity_change' => 10,
        'source' => 'manual',
    ]);

    expect($stockChange)
        ->product_id->toBe($product->id)
        ->quantity_change->toBe(10)
        ->source->toBe('manual');

    $product->refresh();
    expect($product->stock_quantity)->toBe(10);
});

it('handles negative stock changes', function () {
    $product = Product::factory()->create(['stock_quantity' => 20]);
    
    $stockChange = StockChange::create([
        'product_id' => $product->id,
        'quantity_change' => -5,
        'source' => 'webhook',
    ]);

    $product->refresh();
    expect($product->stock_quantity)->toBe(15);
});
