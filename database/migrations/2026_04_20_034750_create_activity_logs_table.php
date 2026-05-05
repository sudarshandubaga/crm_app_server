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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('firm_id')->constrained()->cascadeOnDelete();

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->morphs('subject'); // lead, contact etc

            $table->string('action'); // created, updated, deleted, stage_changed

            $table->json('properties')->nullable();
            // old vs new values

            $table->timestamps();

            $table->index(['subject_id', 'subject_type']);
        });

        /*
        Example JSON
        {
            "old": {"stage": "New"},
            "new": {"stage": "Contacted"}
        }
        */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
