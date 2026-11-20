<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_cases', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('passport_number');
            $table->string('ticket_number')->unique();
            $table->enum('status', ['reception', 'theory', 'practical', 'review', 'completed', 'returned', 'cancelled'])->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_cases');
    }
};
