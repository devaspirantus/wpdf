<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->text('evaluator_signature')->nullable()->change();
            $table->text('worker_signature')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->text('evaluator_signature')->nullable(false)->change();
            $table->text('worker_signature')->nullable(false)->change();
        });
    }
};
