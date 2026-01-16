<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instance_state', function (Blueprint $table): void {
            $table->id();
            $table->uuid('instance_uuid')->unique();
            $table->dateTime('registered_at')->nullable();
            $table->dateTime('last_heartbeat_at')->nullable();
            $table->dateTime('last_license_refresh_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instance_state');
    }
};
