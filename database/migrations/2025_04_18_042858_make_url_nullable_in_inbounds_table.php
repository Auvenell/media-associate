<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('inbounds', function (Blueprint $table) {
            $table->string('url')->nullable()->change();
            $table->text('notes')->nullable()->change();
            $table->text('summary')->nullable()->change();
            $table->string('source')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('inbounds', function (Blueprint $table) {
            $table->string('url')->nullable(false)->change();
            $table->text('notes')->nullable(false)->change();
            $table->text('summary')->nullable(false)->change();
            $table->string('source')->nullable(false)->change();
        });
    }
};
