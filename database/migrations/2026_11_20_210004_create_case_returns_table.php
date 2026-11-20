<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('worker_cases');
            $table->enum('returned_to_station', ['reception', 'theory', 'practical']);
            $table->text('note');
            $table->foreignId('returned_by')->constrained('users');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_returns');
    }
};
