<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_code')->unique(); // PRN-YYYYMMDD-NNNN
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('printer_id')->constrained()->cascadeOnDelete();
            $table->string('original_filename');
            $table->string('stored_original_path')->nullable();
            $table->string('converted_pdf_path')->nullable();
            $table->string('file_type'); // pdf, docx, xlsx, jpg, etc
            $table->unsignedBigInteger('file_size'); // bytes
            $table->unsignedInteger('page_count')->nullable();
            $table->unsignedInteger('copies')->default(1);
            $table->string('paper_size')->default('A4'); // A4, Legal, F4
            $table->string('orientation')->default('portrait'); // portrait, landscape
            $table->string('duplex')->default('none'); // none, long-edge, short-edge
            $table->string('color_mode')->default('grayscale'); // color, grayscale
            $table->string('page_range')->nullable(); // e.g. "1-5", "1,3,5-10"
            $table->string('status')->default('waiting'); // waiting, processing, printing, completed, failed, cancelled
            $table->string('cups_job_id')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('processing_started_at')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_jobs');
    }
};
