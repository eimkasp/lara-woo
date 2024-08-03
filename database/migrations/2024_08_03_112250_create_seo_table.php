<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('seo', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('title');
            $table->text('description')->nullable();
            $table->text('keywords')->nullable();
            $table->string('follow_type')->nullable();
            $table->json('sociale')->nullable();
            $table->json('params')->nullable();

            $table->uuid('seo_model_id')->unique();
            $table->string('seo_model_type');

            $table->timestamps();
        });
    }
};
