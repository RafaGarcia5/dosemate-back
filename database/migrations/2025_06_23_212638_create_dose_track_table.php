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
        Schema::create('dose_track', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('medicament_id');
            $table->datetime('schedule');
            $table->boolean('taken_dose')->default(false);
            $table->timestamp('taken_time')->nullable();
            $table->timestamps();

            $table->foreign('medicament_id')
                  ->references('id')->on('medicament')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dose_track');
    }
};
