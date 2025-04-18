<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('inbounds', function (Blueprint $table) {
            $table->string('text_path')->nullable();
        });
    }

    public function down()
    {
        Schema::table('inbounds', function (Blueprint $table) {
            $table->dropColumn('text_path');
        });
    }
};
