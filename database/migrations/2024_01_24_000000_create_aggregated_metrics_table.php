<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aggregated_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('bucket', 16);
            $table->timestamp('period_start');
            $table->decimal('value', 20, 6);
            $table->json('dimensions')->nullable();
            $table->timestamps();
            $table->unique(['name', 'bucket', 'period_start']);
        });

        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('metric_name');
            $table->decimal('threshold', 20, 6);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
        Schema::dropIfExists('aggregated_metrics');
    }
};
