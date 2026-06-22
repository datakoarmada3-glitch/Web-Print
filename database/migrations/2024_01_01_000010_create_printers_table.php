<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('printers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Canon iR2625
            $table->string('cups_name')->unique(); // canon_ir2625
            $table->string('driver')->nullable();
            $table->string('connection_uri'); // ipp://10.3.105.224/ipp/print
            $table->string('ip_address')->nullable();
            $table->string('location')->nullable();
            $table->string('status')->default('unknown'); // unknown, online, offline, idle, printing, error, paused
            $table->json('capabilities')->nullable(); // paper sizes, duplex, color
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('printers');
    }
};
