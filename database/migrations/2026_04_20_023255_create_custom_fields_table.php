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
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_field_category_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('firm_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->boolean('is_required')->default(0);
            $table->string('type')->default('text');
            $table->longText('options')->nullable()->comment('list, radio, checkbox, etc.');
            $table->string('default_value')->nullable();
            $table->longText('extra_attr')->nullable()->comment('like class, id, etc.');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_fields');
    }
};
