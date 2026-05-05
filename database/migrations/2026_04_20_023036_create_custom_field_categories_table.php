<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('custom_field_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('firm_id')->constrained()->cascadeOnDelete();
            $table->string('for')->default('lead');
            $table->unique(['name', 'firm_id', 'for']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_field_categories');
    }
};
