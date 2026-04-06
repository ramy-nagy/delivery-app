<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('fcm_token')->unique();
            $table->string('device_id')->nullable();
            $table->string('device_type')->nullable(); // ios, android, web
            $table->string('device_name')->nullable();
            $table->string('os_version')->nullable();
            $table->string('app_version')->nullable();
            $table->text('topics')->nullable(); // json array of subscribed topics
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('last_registered_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('user_id');
            $table->index('fcm_token');
            $table->index('device_id');
            $table->index(['user_id', 'is_active']);
            $table->index('last_used_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_device_tokens');
    }
};
