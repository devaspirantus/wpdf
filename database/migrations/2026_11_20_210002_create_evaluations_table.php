<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->unique()->constrained('worker_cases');
            $table->string('task');
            $table->enum('difficulty', ['easy', 'medium', 'hard']);
            $table->json('scores');
            $table->string('evaluator_name');
            $table->string('evaluator_id_number');
            $table->string('evaluator_signature');
            $table->string('worker_signature');
            $table->timestamp('signed_at')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
