<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBillingAndOriginalOrderDateToOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('billing_first_name')->nullable();
            $table->string('billing_last_name')->nullable();
            $table->string('billing_address_1')->nullable();
            $table->string('billing_address_2')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_state')->nullable();
            $table->string('billing_postcode')->nullable();
            $table->string('billing_country')->nullable();
            $table->string('billing_email')->nullable();
            $table->string('billing_phone')->nullable();
            $table->dateTime('original_order_date')->nullable();
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'billing_first_name',
                'billing_last_name',
                'billing_address_1',
                'billing_address_2',
                'billing_city',
                'billing_state',
                'billing_postcode',
                'billing_country',
                'billing_email',
                'billing_phone',
                'original_order_date',
            ]);
        });
    }
}

