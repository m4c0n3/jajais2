<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cp_entitlements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('instance_id')->constrained('cp_instances')->cascadeOnDelete();
            $table->string('module_id');
            $table->dateTime('valid_to')->nullable();
            $table->dateTime('grace_to')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cp_entitlements');
    }
};
