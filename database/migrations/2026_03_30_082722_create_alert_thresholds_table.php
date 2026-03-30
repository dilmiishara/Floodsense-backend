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
        Schema::create('alert_thresholds', function (Blueprint $table) {
           $table->id();
            $table->foreignId('area_id')->constrained('areas')->onDelete('cascade');
            $table->decimal('warning_level', 8, 2);
            $table->decimal('critical_level', 8, 2);
            $table->string('parameter_name')->default('Water Level');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alert_thresholds');
    }
};
