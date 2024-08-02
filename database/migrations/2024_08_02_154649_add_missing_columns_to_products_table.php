<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingColumnsToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Add the missing columns
            // $table->string('sku')->index()->nullable()->after('woocommerce_id');
            $table->string('name')->nullable()->after('sku');
            $table->decimal('price', 10, 2)->nullable()->after('name');
            $table->integer('stock_quantity')->nullable()->after('price');
            $table->unsignedBigInteger('channel_id')->nullable()->after('stock_quantity');
            $table->string('channel')->nullable()->after('channel_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop the columns if we rollback the migration
            $table->dropColumn('sku');
            $table->dropColumn('name');
            $table->dropColumn('price');
            $table->dropColumn('stock_quantity');
            $table->dropColumn('channel_id');
            $table->dropColumn('channel');
        });
    }
}

