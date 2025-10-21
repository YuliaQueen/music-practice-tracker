<?php

declare(strict_types=1);

namespace App\Services;

use App\Domains\Planning\Models\Session;
use App\Domains\Planning\Models\SessionBlock;
use App\Enums\ExerciseType;
use App\Enums\PeriodType;
use App\Enums\SessionBlockStatus;
use App\Enums\SessionStatus;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class StatisticsService
{
    /**
     * Получить общую статистику для всех периодов
     */
    public function getAllPeriodStatistics(int $userId): array
    {
        return [
            'day'   => $this->getPeriodStatistics($userId, PeriodType::DAY),
            'week'  => $this->getPeriodStatistics($userId, PeriodType::WEEK),
            'month' => $this->getPeriodStatistics($userId, PeriodType::MONTH),
            'year'  => $this->getPeriodStatistics($userId, PeriodType::YEAR),
        ];
    }

    /**
     * Получить статистику для конкретного периода
     */
    public function getPeriodStatistics(int $userId, PeriodType $period): array
    {
        $dateRange = $period->getDateRange();

        $sessions = $this->getCompletedSessions($userId, $dateRange['start'], $dateRange['end']);

        $totalMinutes = $sessions->sum('actual_duration') ?? 0;
        $sessionsCount = $sessions->count();

        $result = [
            'total_minutes'  => $totalMinutes,
            'sessions_count' => $sessionsCount,
        ];

        // Добавляем детальную статистику в зависимости от периода
        return match ($period) {
            PeriodType::DAY => array_merge($result, [
                'date' => $dateRange['start']->format('Y-m-d'),
                'exercise_stats' => $this->getExerciseStatsForPeriod($userId, $dateRange['start'], $dateRange['end']),
            ]),
            PeriodType::WEEK => array_merge($result, [
                'start_date'  => $dateRange['start']->format('Y-m-d'),
                'end_date'    => $dateRange['end']->format('Y-m-d'),
                'daily_stats' => $this->getDailyStatsForWeek($sessions, $dateRange['start']),
            ]),
            PeriodType::MONTH => array_merge($result, [
                'start_date'   => $dateRange['start']->format('Y-m-d'),
                'end_date'     => $dateRange['end']->format('Y-m-d'),
                'weekly_stats' => $this->getWeeklyStatsForMonth($sessions, $dateRange['start'], $dateRange['end']),
            ]),
            PeriodType::YEAR => array_merge($result, [
                'year'          => $dateRange['start']->year,
                'monthly_stats' => $this->getMonthlyStatsForYear($sessions, $dateRange['start']),
            ]),
        };
    }

    /**
     * Получить данные для всех графиков
     */
    public function getChartData(int $userId, PeriodType $period): array
    {
        return [
            'daily_practice'  => $this->getDailyPracticeChart($userId, 30),
            'weekly_practice' => $this->getWeeklyPracticeChart($userId, 12),
            'exercise_types'  => $this->getExerciseTypesChart($userId, $period),
            'practice_streak' => $this->getPracticeStreak($userId),
        ];
    }

    /**
     * Получить завершенные сессии за период
     */
    private function getCompletedSessions(int $userId, Carbon $startDate, Carbon $endDate): Collection
    {
        return Session::where('user_id', $userId)
            ->whereBetween('started_at', [$startDate, $endDate])
            ->where('status', SessionStatus::COMPLETED->value)
            ->get();
    }

    /**
     * Получить статистику по упражнениям за период
     */
    private function getExerciseStatsForPeriod(int $userId, Carbon $startDate, Carbon $endDate): Collection
    {
        return SessionBlock::whereHas('session', function ($query) use ($userId, $startDate, $endDate) {
            $query->where('user_id', $userId)
                ->whereBetween('started_at', [$startDate, $endDate])
                ->where('status', SessionStatus::COMPLETED->value);
        })
            ->where('status', SessionBlockStatus::COMPLETED->value)
            ->selectRaw('type, SUM(actual_duration) as total_duration, COUNT(*) as count')
            ->groupBy('type')
            ->get()
            ->keyBy('type');
    }

    /**
     * Получить статистику по дням для недели (оптимизированная версия)
     */
    private function getDailyStatsForWeek(Collection $sessions, Carbon $startOfWeek): array
    {
        // Группируем сессии по датам для быстрого доступа
        $sessionsByDate = $sessions->groupBy(fn($session) =>
            $session->started_at?->format('Y-m-d')
        );

        $dailyStats = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $dateKey = $date->format('Y-m-d');
            $daySessions = $sessionsByDate->get($dateKey, collect());

            $dailyStats[] = [
                'date'     => $dateKey,
                'day_name' => $date->format('l'),
                'minutes'  => $daySessions->sum('actual_duration') ?? 0,
                'sessions' => $daySessions->count(),
            ];
        }

        return $dailyStats;
    }

    /**
     * Получить статистику по неделям для месяца (оптимизированная версия)
     */
    private function getWeeklyStatsForMonth(Collection $sessions, Carbon $startOfMonth, Carbon $endOfMonth): array
    {
        $weeklyStats = [];
        $currentWeek = $startOfMonth->copy()->startOfWeek();

        while ($currentWeek->lte($endOfMonth)) {
            $weekEnd = $currentWeek->copy()->endOfWeek();
            if ($weekEnd->gt($endOfMonth)) {
                $weekEnd = $endOfMonth->copy();
            }

            // Фильтруем сессии для текущей недели
            $weekSessions = $sessions->filter(function ($session) use ($currentWeek, $weekEnd) {
                return $session->started_at &&
                    $session->started_at->gte($currentWeek) &&
                    $session->started_at->lte($weekEnd);
            });

            $weeklyStats[] = [
                'week_start' => $currentWeek->format('Y-m-d'),
                'week_end'   => $weekEnd->format('Y-m-d'),
                'minutes'    => $weekSessions->sum('actual_duration') ?? 0,
                'sessions'   => $weekSessions->count(),
            ];

            $currentWeek->addWeek();
        }

        return $weeklyStats;
    }

    /**
     * Получить статистику по месяцам для года (оптимизированная версия)
     */
    private function getMonthlyStatsForYear(Collection $sessions, Carbon $startOfYear): array
    {
        // Группируем сессии по месяцам для быстрого доступа
        $sessionsByMonth = $sessions->groupBy(fn($session) =>
            $session->started_at?->month
        );

        $monthlyStats = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthStart = Carbon::create($startOfYear->year, $i, 1)->startOfMonth();
            $monthSessions = $sessionsByMonth->get($i, collect());

            $monthlyStats[] = [
                'month'      => $i,
                'month_name' => $monthStart->format('F'),
                'minutes'    => $monthSessions->sum('actual_duration') ?? 0,
                'sessions'   => $monthSessions->count(),
            ];
        }

        return $monthlyStats;
    }

    /**
     * Получить данные для графика ежедневной практики
     */
    public function getDailyPracticeChart(int $userId, int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days - 1)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // Получаем статистику агрегированную по дням из БД
        $statistics = Session::where('user_id', $userId)
            ->whereBetween('started_at', [$startDate, $endDate])
            ->where('status', SessionStatus::COMPLETED->value)
            ->selectRaw('DATE(started_at) as date, SUM(actual_duration) as total_minutes, COUNT(*) as sessions_count')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Генерируем данные для всех дней (включая дни без практики)
        $chartData = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $dateKey = $currentDate->format('Y-m-d');
            $dayStats = $statistics->get($dateKey);

            $chartData[] = [
                'date'     => $dateKey,
                'minutes'  => $dayStats?->total_minutes ?? 0,
                'sessions' => $dayStats?->sessions_count ?? 0,
            ];

            $currentDate->addDay();
        }

        return $chartData;
    }

    /**
     * Получить данные для графика еженедельной практики
     */
    public function getWeeklyPracticeChart(int $userId, int $weeks = 12): array
    {
        $startDate = Carbon::now()->subWeeks($weeks - 1)->startOfWeek();
        $endDate = Carbon::now()->endOfWeek();

        $sessions = $this->getCompletedSessions($userId, $startDate, $endDate);

        $chartData = [];
        $currentWeek = $startDate->copy();

        while ($currentWeek->lte($endDate)) {
            $weekEnd = $currentWeek->copy()->endOfWeek();

            // Фильтруем сессии для текущей недели
            $weekSessions = $sessions->filter(function ($session) use ($currentWeek, $weekEnd) {
                return $session->started_at &&
                    $session->started_at->gte($currentWeek) &&
                    $session->started_at->lte($weekEnd);
            });

            $chartData[] = [
                'week_start' => $currentWeek->format('Y-m-d'),
                'week_end'   => $weekEnd->format('Y-m-d'),
                'minutes'    => $weekSessions->sum('actual_duration') ?? 0,
                'sessions'   => $weekSessions->count(),
            ];

            $currentWeek->addWeek();
        }

        return $chartData;
    }

    /**
     * Получить статистику по типам упражнений
     */
    public function getExerciseTypesChart(int $userId, PeriodType $period): array
    {
        $dateRange = $period->getDateRange();

        $stats = SessionBlock::whereHas('session', function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->where('status', SessionStatus::COMPLETED->value);
        })
            ->where('status', SessionBlockStatus::COMPLETED->value)
            ->whereBetween('completed_at', [$dateRange['start'], $dateRange['end']])
            ->selectRaw('type, SUM(actual_duration) as total_duration, COUNT(*) as count')
            ->groupBy('type')
            ->get();

        return $stats->map(function ($item) {
            return [
                'type'       => $item->type,
                'type_label' => ExerciseType::tryFrom($item->type)?->label() ?? $item->type,
                'minutes'    => $item->total_duration ?? 0,
                'count'      => $item->count,
            ];
        })->toArray();
    }

    /**
     * Получить серию практики (дни подряд)
     */
    public function getPracticeStreak(int $userId): array
    {
        // Получаем уникальные даты практики за последний год
        $practiceDates = Session::where('user_id', $userId)
            ->where('status', SessionStatus::COMPLETED->value)
            ->where('started_at', '>=', Carbon::now()->subYear())
            ->selectRaw('DISTINCT DATE(started_at) as practice_date')
            ->orderBy('practice_date', 'desc')
            ->pluck('practice_date')
            ->map(fn($date) => Carbon::parse($date))
            ->values();

        if ($practiceDates->isEmpty()) {
            return [
                'current_streak'      => 0,
                'longest_streak'      => 0,
                'total_practice_days' => 0,
            ];
        }

        // Подсчитываем текущую серию
        $currentStreak = 0;
        $checkDate = Carbon::today();

        foreach ($practiceDates as $practiceDate) {
            if ($practiceDate->isSameDay($checkDate)) {
                $currentStreak++;
                $checkDate->subDay();
            } else {
                break;
            }
        }

        // Подсчитываем самую длинную серию
        $longestStreak = 1;
        $tempStreak = 1;

        for ($i = 1; $i < $practiceDates->count(); $i++) {
            $current = $practiceDates[$i];
            $previous = $practiceDates[$i - 1];

            if ($previous->diffInDays($current) === 1) {
                $tempStreak++;
                $longestStreak = max($longestStreak, $tempStreak);
            } else {
                $tempStreak = 1;
            }
        }

        return [
            'current_streak'      => $currentStreak,
            'longest_streak'      => $longestStreak,
            'total_practice_days' => $practiceDates->count(),
        ];
    }
}
