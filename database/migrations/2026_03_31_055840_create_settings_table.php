<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('settings', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->string('section', 50);
        $table->string('key_name', 100);
        $table->text('value');
        $table->timestampsTz();
        $table->unique(['section', 'key_name']);
    });
}

};
