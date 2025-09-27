<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Запустить миграцию
     */
    public function up(): void
    {
        Schema::create('practice_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('practice_template_id')->nullable()->constrained('practice_templates')->nullOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('planned_duration')->comment('Запланированная длительность в минутах');
            $table->integer('actual_duration')->nullable()->comment('Фактическая длительность в минутах');

            $table->enum('status', ['planned', 'active', 'paused', 'completed', 'cancelled'])
                ->default('planned');

            $table->timestamp('scheduled_for')->nullable()->comment('Запланированное время начала');
            $table->timestamp('started_at')->nullable()->comment('Фактическое время начала');
            $table->timestamp('completed_at')->nullable()->comment('Время завершения');

            $table->json('metadata')->nullable()->comment('Дополнительные данные сессии');

            $table->timestamps();
            $table->softDeletes();

            // Индексы для производительности
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'started_at']);
            $table->index(['user_id', 'completed_at']);
            $table->index(['scheduled_for']);
            $table->index(['created_at']);
            $table->index(['status']);
        });
    }

    /**
     * Откатить миграцию
     */
    public function down(): void
    {
        Schema::dropIfExists('practice_sessions');
    }
};
