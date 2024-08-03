<?php 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVariationsTable extends Migration
{
    public function up()
    {
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('sku')->unique();
            $table->string('name')->nullable();
            $table->decimal('price', 8, 2)->default(0.00);
            $table->integer('stock_quantity')->default(0)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_variations');
    }
}
