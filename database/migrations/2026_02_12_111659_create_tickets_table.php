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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('type');
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('quantity');
            $table->timestamps();

            $table->unique(['event_id', 'type']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreign('ticket_id')->references('id')->on('tickets')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['ticket_id']);
        });

        Schema::dropIfExists('tickets');
    }
};
