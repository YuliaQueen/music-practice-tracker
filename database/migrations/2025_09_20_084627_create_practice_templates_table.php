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
        Schema::create('practice_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->text('description')->nullable();

            $table->enum('category', [
                'beginner',
                'intermediate',
                'advanced',
                'technique_focus',
                'repertoire_focus',
                'quick_practice',
                'warm_up',
                'custom'
            ])->default('custom');

            $table->enum('difficulty_level', ['beginner', 'intermediate', 'advanced', 'expert'])
                ->default('beginner');

            $table->integer('total_duration')->default(0)->comment('Общая длительность в минутах');

            $table->boolean('is_public')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->integer('usage_count')->default(0);

            $table->json('tags')->nullable()->comment('Теги для поиска');
            $table->json('metadata')->nullable()->comment('Дополнительные данные');

            $table->timestamps();
            $table->softDeletes();

            // Индексы для производительности
            $table->index(['user_id', 'is_public']);
            $table->index(['category', 'difficulty_level']);
            $table->index(['is_public', 'is_featured']);
            $table->index(['total_duration']);
            $table->index(['usage_count']);
            $table->index(['created_at']);
            $table->index(['category']);
            $table->index(['difficulty_level']);
            $table->index(['is_public']);
            $table->index(['is_featured']);
        });
    }

    /**
     * Откатить миграцию
     */
    public function down(): void
    {
        Schema::dropIfExists('practice_templates');
    }
};
