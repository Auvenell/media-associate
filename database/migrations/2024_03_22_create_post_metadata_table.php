<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('post_metadata', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inbound_id')->constrained('inbounds')->onDelete('cascade');
            $table->json('categories')->nullable();
            $table->enum('sentiment', ['neutral', 'bullish', 'bearish'])->default('neutral');
            $table->enum('market_mover', ['yes', 'no'])->default('no');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('post_metadata');
    }
};
