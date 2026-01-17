<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cp_signing_keys', function (Blueprint $table): void {
            $table->id();
            $table->string('kid')->unique();
            $table->text('public_key');
            $table->text('private_key_encrypted');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cp_signing_keys');
    }
};
