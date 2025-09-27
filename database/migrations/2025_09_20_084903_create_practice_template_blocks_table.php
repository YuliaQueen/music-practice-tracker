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
        Schema::create('practice_template_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('practice_template_id')->constrained('practice_templates')->cascadeOnDelete();

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

            $table->integer('duration')->comment('Длительность в минутах');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_mandatory')->default(true);

            $table->json('settings')->nullable()->comment('Настройки блока');
            $table->json('default_content')->nullable()->comment('Содержимое по умолчанию');

            $table->timestamps();
            $table->softDeletes();

            // Индексы для производительности
            $table->index(['practice_template_id', 'sort_order']);
            $table->index(['practice_template_id', 'type']);
            $table->index(['type', 'is_mandatory']);
            $table->index(['type']);
            $table->index(['sort_order']);
        });
    }

    /**
     * Откатить миграцию
     */
    public function down(): void
    {
        Schema::dropIfExists('practice_template_blocks');
    }
};
