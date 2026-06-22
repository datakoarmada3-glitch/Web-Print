<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_job_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('print_job_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->text('message')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['print_job_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_job_logs');
    }
};
