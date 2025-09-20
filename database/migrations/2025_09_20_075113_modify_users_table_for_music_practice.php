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
        Schema::table('users', function (Blueprint $table) {
            $table->string('timezone')->default('UTC')->after('password');
            $table->json('preferences')->nullable()->comment('Пользовательские настройки и предпочтения')->after('timezone');
            $table->integer('total_sessions')->default(0)->comment('Общее количество сессий')->after('preferences');
            $table->integer('total_practice_minutes')->default(0)->comment('Общее время занятий в минутах')->after('total_sessions');
            $table->date('last_practice_date')->nullable()->comment('Дата последнего занятия')->after('total_practice_minutes');

            $table->softDeletes()->after('updated_at');

            // Индексы для производительности
            $table->index(['last_practice_date']);
            $table->index(['created_at']);
            $table->index(['deleted_at']);
        });
    }

    /**
     * Откатить миграцию
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['last_practice_date']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['deleted_at']);

            $table->dropSoftDeletes();
            $table->dropColumn([
                'timezone',
                'preferences',
                'total_sessions',
                'total_practice_minutes',
                'last_practice_date'
            ]);
        });
    }
};
