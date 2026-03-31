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
        Schema::table('customer_addresses', function (Blueprint $table) {
            // Rename region to governorate
            $table->renameColumn('region', 'governorate');
            
            // Drop unnecessary columns
            $table->dropColumn('line2');
            $table->dropColumn('postal_code');
            $table->dropColumn('country');
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_addresses', function (Blueprint $table) {
            // Add back the dropped columns
            $table->string('line2')->nullable()->after('line1');
            $table->string('postal_code', 32)->nullable()->after('city');
            $table->string('country', 2)->nullable()->after('postal_code');
            $table->float('latitude')->nullable()->after('country');
            $table->float('longitude')->nullable()->after('latitude');
            
            // Rename governorate back to region
            $table->renameColumn('governorate', 'region');
        });
    }
};
