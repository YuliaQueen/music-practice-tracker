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
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title'); // Название цели
            $table->text('description')->nullable(); // Описание цели
            $table->string('type'); // Тип цели: daily_minutes, weekly_sessions, streak_days, exercise_type
            $table->json('target'); // Целевые значения: {"minutes": 30, "days": 7}
            $table->json('progress')->nullable(); // Текущий прогресс: {"current": 25, "total": 30}
            $table->date('start_date'); // Дата начала цели
            $table->date('end_date')->nullable(); // Дата окончания (null = бессрочная)
            $table->boolean('is_active')->default(true); // Активна ли цель
            $table->boolean('is_completed')->default(false); // Выполнена ли цель
            $table->timestamp('completed_at')->nullable(); // Когда была выполнена
            $table->timestamps();
            
            // Индексы для быстрого поиска
            $table->index(['user_id', 'is_active']);
            $table->index(['type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};
