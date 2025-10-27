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
        Schema::table('practice_sessions', function (Blueprint $table) {
            $table->enum('session_mode', ['standard', 'pomodoro'])->default('standard')->after('auto_advance');
            $table->boolean('pomodoro_enabled')->default(false)->after('session_mode');
            $table->unsignedInteger('pomodoro_work_duration')->default(25)->after('pomodoro_enabled')->comment('Длительность работы в минутах');
            $table->unsignedInteger('pomodoro_short_break')->default(5)->after('pomodoro_work_duration')->comment('Короткий перерыв в минутах');
            $table->unsignedInteger('pomodoro_long_break')->default(15)->after('pomodoro_short_break')->comment('Длинный перерыв в минутах');
            $table->unsignedInteger('pomodoro_cycles_before_long_break')->default(4)->after('pomodoro_long_break')->comment('Циклов до длинного перерыва');
            $table->unsignedInteger('pomodoro_completed_cycles')->default(0)->after('pomodoro_cycles_before_long_break')->comment('Завершенные циклы');
            $table->unsignedInteger('pomodoro_total_cycles')->default(0)->after('pomodoro_completed_cycles')->comment('Всего запланировано циклов');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('practice_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'session_mode',
                'pomodoro_enabled',
                'pomodoro_work_duration',
                'pomodoro_short_break',
                'pomodoro_long_break',
                'pomodoro_cycles_before_long_break',
                'pomodoro_completed_cycles',
                'pomodoro_total_cycles',
            ]);
        });
    }
};
