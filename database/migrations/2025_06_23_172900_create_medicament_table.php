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
        Schema::create('medicament', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('dosage', 50);
            $table->integer('interval_hours');
            $table->datetime('start_date');
            $table->date('end_date');
            $table->text('comment')->nullable();

            $table->unsignedBigInteger('treatment_id');
            $table->foreign('treatment_id')
                  ->references('id')->on('treatment')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicament');
    }
};
