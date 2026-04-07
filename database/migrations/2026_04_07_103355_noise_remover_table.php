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
        Schema::create('noise_remover', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('title');
             $table->string('slug')->unique();
            $table->string('media_name')->nullable();
            $table->string('media_url')->nullable();
            $table->enum('status', ['draft', 'processing', 'completed', 'failed'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('noise_remover');
    }
};
