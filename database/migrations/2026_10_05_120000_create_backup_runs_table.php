<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_runs', function (Blueprint $table) {
            $table->id();
            $table->string('filename')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('status', 20)->default('running')->index();
            $table->string('trigger', 20)->default('manual');
            $table->boolean('includes_files')->default(true);
            $table->string('zip_password')->nullable();
            $table->text('message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_runs');
    }
};
