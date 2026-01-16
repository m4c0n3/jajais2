<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_tokens', function (Blueprint $table): void {
            $table->id();
            $table->dateTime('fetched_at')->nullable();
            $table->dateTime('valid_to');
            $table->dateTime('grace_to')->nullable();
            $table->longText('token');
            $table->json('parsed')->nullable();
            $table->dateTime('revoked_at')->nullable();
            $table->string('last_refresh_status')->nullable();
            $table->text('last_refresh_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_tokens');
    }
};
