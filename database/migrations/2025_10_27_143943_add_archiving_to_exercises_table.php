<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->boolean('is_archived')->default(false)->after('metadata');
            $table->timestamp('archived_at')->nullable()->after('is_archived');
            $table->timestamp('started_learning_at')->nullable()->after('archived_at');
            $table->timestamp('completed_learning_at')->nullable()->after('started_learning_at');

            $table->index('is_archived');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->dropIndex(['is_archived']);
            $table->dropColumn(['is_archived', 'archived_at', 'started_learning_at', 'completed_learning_at']);
        });
    }
};
