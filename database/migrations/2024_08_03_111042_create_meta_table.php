<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMetaTable extends Migration
{
    public function up()
    {
        Schema::create('meta', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->morphs('metable'); // Adds 'metable_id' and 'metable_type' columns
            $table->string('key')->index();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('meta');
    }
}

