<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('station_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('worker_cases');
            $table->enum('station', ['reception', 'theory', 'practical', 'review']);
            $table->foreignId('user_id')->constrained('users');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('station_visits');
    }
};
