<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->after('restaurant_id');
            $table->foreign('category_id')->references('id')->on('restaurant_categories')->nullOnDelete();
            $table->dropColumn('category');
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->string('category')->nullable()->after('restaurant_id');
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};
