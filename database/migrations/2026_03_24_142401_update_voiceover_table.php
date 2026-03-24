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
        Schema::table('voice_over', function (Blueprint $table) {
            // Add a new column
            $table->string('media_url')->after('media_name')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('voice_over', function (Blueprint $table) {
            // Undo adding the new column
            $table->dropColumn('media_url');
        });
    }
};
