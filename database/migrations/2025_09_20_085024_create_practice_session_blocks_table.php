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
        Schema::create('practice_session_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('practice_session_id')->constrained('practice_sessions')->cascadeOnDelete();
            $table->foreignId('practice_template_block_id')->nullable()->constrained('practice_template_blocks')->nullOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            $table->enum('type', [
                'warmup',
                'technique',
                'repertoire',
                'improvisation',
                'sight_reading',
                'theory',
                'break',
                'custom'
            ])->default('custom');

            $table->integer('planned_duration')->comment('Запланированная длительность в минутах');
            $table->integer('actual_duration')->nullable()->comment('Фактическая длительность в минутах');

            $table->enum('status', ['planned', 'active', 'paused', 'completed', 'skipped'])
                ->default('planned');

            $table->integer('sort_order')->default(0);

            $table->timestamp('started_at')->nullable()->comment('Время начала блока');
            $table->timestamp('completed_at')->nullable()->comment('Время завершения блока');

            $table->json('settings')->nullable()->comment('Настройки блока');

            $table->timestamps();
            $table->softDeletes();

            // Индексы для производительности
            $table->index(['practice_session_id', 'sort_order']);
            $table->index(['practice_session_id', 'status']);
            $table->index(['practice_session_id', 'type']);
            $table->index(['type', 'status']);
            $table->index(['started_at']);
            $table->index(['completed_at']);
            $table->index(['type']);
            $table->index(['status']);
            $table->index(['sort_order']);
        });
    }

    /**
     * Откатить миграцию
     */
    public function down(): void
    {
        Schema::dropIfExists('practice_session_blocks');
    }
};
