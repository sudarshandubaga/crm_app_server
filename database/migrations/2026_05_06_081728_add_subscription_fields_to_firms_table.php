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
        Schema::table('firms', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('has_used_first_time_offer')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('firms', function (Blueprint $table) {
            $table->dropConstrainedForeignId('plan_id');
            $table->dropColumn('has_used_first_time_offer');
        });
    }
};
