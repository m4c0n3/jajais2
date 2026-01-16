<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('name');
            $table->boolean('enabled')->default(false);
            $table->string('installed_version')->nullable();
            $table->string('requires_core')->nullable();
            $table->boolean('license_required')->default(false);
            $table->dateTime('last_booted_at')->nullable();
            $table->string('last_boot_status')->nullable();
            $table->text('last_boot_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
