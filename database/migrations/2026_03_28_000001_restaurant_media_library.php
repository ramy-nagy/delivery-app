<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // No columns needed, media is handled by spatie/laravel-medialibrary
    }

    public function down(): void
    {
        // No columns to drop
    }
};
