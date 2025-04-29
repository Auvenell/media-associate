<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inbound_id')->constrained()->cascadeOnDelete();
            $table->string('url');
            $table->string('title');
            $table->text('excerpt');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sources');
    }
};
