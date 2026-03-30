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
        Schema::create('maturity_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('min_score');
            $table->integer('max_score');
            $table->text('description');
            $table->string('color', 7)->default('#6b7280');
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maturity_levels');
    }
};
