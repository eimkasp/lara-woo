<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('woocommerce_id')->nullable();
            $table->decimal('total', 10, 2);
            $table->string('status');
            $table->unsignedBigInteger('channel_id');
            $table->string('channel');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->timestamps();

            $table->unique(['woocommerce_id', 'channel_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
}

