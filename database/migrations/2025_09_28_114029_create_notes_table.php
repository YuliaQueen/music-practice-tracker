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
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('exercise_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('title'); // Название файла нот
            $table->text('description')->nullable(); // Описание/заметки к нотам
            $table->string('filename'); // Оригинальное имя файла
            $table->string('file_path'); // Путь к файлу в хранилище
            $table->string('mime_type'); // MIME тип файла
            $table->bigInteger('file_size'); // Размер файла в байтах
            $table->string('file_hash')->unique(); // Хеш файла для дедупликации
            $table->json('metadata')->nullable(); // Дополнительные метаданные (страницы, теги и т.д.)
            $table->boolean('is_public')->default(false); // Публичный доступ
            $table->timestamps();
            $table->softDeletes();
            
            // Индексы для производительности
            $table->index(['user_id', 'exercise_id']);
            $table->index(['user_id', 'created_at']);
            $table->index(['file_hash']);
            $table->index(['mime_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
