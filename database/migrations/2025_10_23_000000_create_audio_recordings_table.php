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
        Schema::create('audio_recordings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Связь с упражнением или блоком сессии
            $table->foreignId('exercise_id')->nullable()->constrained('exercises')->onDelete('cascade');
            $table->foreignId('practice_session_block_id')->nullable()->constrained('practice_session_blocks')->onDelete('cascade');

            $table->string('title')->nullable();
            $table->text('notes')->nullable()->comment('Заметки к записи');

            // Файл записи
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type')->default('audio/webm');
            $table->integer('file_size')->comment('Размер файла в байтах');
            $table->integer('duration')->nullable()->comment('Длительность записи в секундах');

            // Метаданные
            $table->integer('quality_rating')->nullable()->comment('Оценка качества исполнения от 1 до 5');
            $table->json('metadata')->nullable()->comment('Дополнительные метаданные');

            $table->timestamp('recorded_at')->comment('Время записи');
            $table->timestamps();
            $table->softDeletes();

            // Индексы
            $table->index(['user_id', 'recorded_at']);
            $table->index(['exercise_id', 'recorded_at']);
            $table->index(['practice_session_block_id', 'recorded_at']);
            $table->index(['user_id', 'quality_rating']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audio_recordings');
    }
};
