<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Planning\Models\Session;
use App\Domains\Planning\Models\SessionBlock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StatisticsController extends Controller
{
    /**
     * Показать страницу статистики
     */
    public function index(Request $request): Response
    {
        $userId = auth()->id();
        $period = $request->get('period', 'week'); // day, week, month, year
        
        // Получаем статистику для разных периодов
        $statistics = [
            'day' => $this->getDayStatistics($userId),
            'week' => $this->getWeekStatistics($userId),
            'month' => $this->getMonthStatistics($userId),
            'year' => $this->getYearStatistics($userId),
        ];
        
        // Получаем данные для графиков
        $chartData = [
            'daily_practice' => $this->getDailyPracticeChart($userId, 30),
            'weekly_practice' => $this->getWeeklyPracticeChart($userId, 12),
            'exercise_types' => $this->getExerciseTypesChart($userId, $period),
            'practice_streak' => $this->getPracticeStreak($userId),
        ];
        
        return Inertia::render('Statistics/Index', [
            'statistics' => $statistics,
            'chartData' => $chartData,
            'currentPeriod' => $period,
        ]);
    }
    
    /**
     * Статистика за день
     */
    private function getDayStatistics(int $userId): array
    {
        $today = Carbon::today();
        
        $sessions = Session::where('user_id', $userId)
            ->whereDate('started_at', $today)
            ->where('status', Session::STATUS_COMPLETED)
            ->get();
            
        $totalMinutes = $sessions->sum('actual_duration') ?? 0;
        $sessionsCount = $sessions->count();
        
        // Статистика по типам упражнений
        $exerciseStats = SessionBlock::whereHas('session', function ($query) use ($userId, $today) {
                $query->where('user_id', $userId)
                      ->whereDate('started_at', $today)
                      ->where('status', Session::STATUS_COMPLETED);
            })
            ->where('status', SessionBlock::STATUS_COMPLETED)
            ->selectRaw('type, SUM(actual_duration) as total_duration, COUNT(*) as count')
            ->groupBy('type')
            ->get()
            ->keyBy('type');
            
        return [
            'total_minutes' => $totalMinutes,
            'sessions_count' => $sessionsCount,
            'exercise_stats' => $exerciseStats,
            'date' => $today->format('Y-m-d'),
        ];
    }
    
    /**
     * Статистика за неделю
     */
    private function getWeekStatistics(int $userId): array
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        
        $sessions = Session::where('user_id', $userId)
            ->whereBetween('started_at', [$startOfWeek, $endOfWeek])
            ->where('status', Session::STATUS_COMPLETED)
            ->get();
            
        $totalMinutes = $sessions->sum('actual_duration') ?? 0;
        $sessionsCount = $sessions->count();
        
        // Статистика по дням недели
        $dailyStats = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $daySessions = $sessions->filter(function ($session) use ($date) {
                return $session->started_at && $session->started_at->isSameDay($date);
            });
            
            $dailyStats[] = [
                'date' => $date->format('Y-m-d'),
                'day_name' => $date->format('l'),
                'minutes' => $daySessions->sum('actual_duration') ?? 0,
                'sessions' => $daySessions->count(),
            ];
        }
        
        return [
            'total_minutes' => $totalMinutes,
            'sessions_count' => $sessionsCount,
            'daily_stats' => $dailyStats,
            'start_date' => $startOfWeek->format('Y-m-d'),
            'end_date' => $endOfWeek->format('Y-m-d'),
        ];
    }
    
    /**
     * Статистика за месяц
     */
    private function getMonthStatistics(int $userId): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        $sessions = Session::where('user_id', $userId)
            ->whereBetween('started_at', [$startOfMonth, $endOfMonth])
            ->where('status', Session::STATUS_COMPLETED)
            ->get();
            
        $totalMinutes = $sessions->sum('actual_duration') ?? 0;
        $sessionsCount = $sessions->count();
        
        // Статистика по неделям месяца
        $weeklyStats = [];
        $currentWeek = $startOfMonth->copy()->startOfWeek();
        
        while ($currentWeek->lte($endOfMonth)) {
            $weekEnd = $currentWeek->copy()->endOfWeek();
            if ($weekEnd->gt($endOfMonth)) {
                $weekEnd = $endOfMonth;
            }
            
            $weekSessions = $sessions->filter(function ($session) use ($currentWeek, $weekEnd) {
                return $session->started_at && 
                       $session->started_at->gte($currentWeek) && 
                       $session->started_at->lte($weekEnd);
            });
            
            $weeklyStats[] = [
                'week_start' => $currentWeek->format('Y-m-d'),
                'week_end' => $weekEnd->format('Y-m-d'),
                'minutes' => $weekSessions->sum('actual_duration') ?? 0,
                'sessions' => $weekSessions->count(),
            ];
            
            $currentWeek->addWeek();
        }
        
        return [
            'total_minutes' => $totalMinutes,
            'sessions_count' => $sessionsCount,
            'weekly_stats' => $weeklyStats,
            'start_date' => $startOfMonth->format('Y-m-d'),
            'end_date' => $endOfMonth->format('Y-m-d'),
        ];
    }
    
    /**
     * Статистика за год
     */
    private function getYearStatistics(int $userId): array
    {
        $startOfYear = Carbon::now()->startOfYear();
        $endOfYear = Carbon::now()->endOfYear();
        
        $sessions = Session::where('user_id', $userId)
            ->whereBetween('started_at', [$startOfYear, $endOfYear])
            ->where('status', Session::STATUS_COMPLETED)
            ->get();
            
        $totalMinutes = $sessions->sum('actual_duration') ?? 0;
        $sessionsCount = $sessions->count();
        
        // Статистика по месяцам года
        $monthlyStats = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthStart = Carbon::create($startOfYear->year, $i, 1)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
            
            $monthSessions = $sessions->filter(function ($session) use ($monthStart, $monthEnd) {
                return $session->started_at && 
                       $session->started_at->gte($monthStart) && 
                       $session->started_at->lte($monthEnd);
            });
            
            $monthlyStats[] = [
                'month' => $i,
                'month_name' => $monthStart->format('F'),
                'minutes' => $monthSessions->sum('actual_duration') ?? 0,
                'sessions' => $monthSessions->count(),
            ];
        }
        
        return [
            'total_minutes' => $totalMinutes,
            'sessions_count' => $sessionsCount,
            'monthly_stats' => $monthlyStats,
            'year' => $startOfYear->year,
        ];
    }
    
    /**
     * Данные для графика ежедневной практики
     */
    private function getDailyPracticeChart(int $userId, int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days - 1)->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        
        $sessions = Session::where('user_id', $userId)
            ->whereBetween('started_at', [$startDate, $endDate])
            ->where('status', Session::STATUS_COMPLETED)
            ->get();
            
        $chartData = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            $daySessions = $sessions->filter(function ($session) use ($currentDate) {
                return $session->started_at && $session->started_at->isSameDay($currentDate);
            });
            
            $chartData[] = [
                'date' => $currentDate->format('Y-m-d'),
                'minutes' => $daySessions->sum('actual_duration') ?? 0,
                'sessions' => $daySessions->count(),
            ];
            
            $currentDate->addDay();
        }
        
        return $chartData;
    }
    
    /**
     * Данные для графика еженедельной практики
     */
    private function getWeeklyPracticeChart(int $userId, int $weeks = 12): array
    {
        $startDate = Carbon::now()->subWeeks($weeks - 1)->startOfWeek();
        $endDate = Carbon::now()->endOfWeek();
        
        $sessions = Session::where('user_id', $userId)
            ->whereBetween('started_at', [$startDate, $endDate])
            ->where('status', Session::STATUS_COMPLETED)
            ->get();
            
        $chartData = [];
        $currentWeek = $startDate->copy();
        
        while ($currentWeek->lte($endDate)) {
            $weekEnd = $currentWeek->copy()->endOfWeek();
            $weekSessions = $sessions->filter(function ($session) use ($currentWeek, $weekEnd) {
                return $session->started_at && 
                       $session->started_at->gte($currentWeek) && 
                       $session->started_at->lte($weekEnd);
            });
            
            $chartData[] = [
                'week_start' => $currentWeek->format('Y-m-d'),
                'week_end' => $weekEnd->format('Y-m-d'),
                'minutes' => $weekSessions->sum('actual_duration') ?? 0,
                'sessions' => $weekSessions->count(),
            ];
            
            $currentWeek->addWeek();
        }
        
        return $chartData;
    }
    
    /**
     * Статистика по типам упражнений
     */
    private function getExerciseTypesChart(int $userId, string $period): array
    {
        $query = SessionBlock::whereHas('session', function ($q) use ($userId) {
            $q->where('user_id', $userId)
              ->where('status', Session::STATUS_COMPLETED);
        })->where('status', SessionBlock::STATUS_COMPLETED);
        
        // Применяем фильтр по периоду
        switch ($period) {
            case 'day':
                $query->whereDate('completed_at', Carbon::today());
                break;
            case 'week':
                $query->whereBetween('completed_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
                break;
            case 'month':
                $query->whereBetween('completed_at', [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfMonth()
                ]);
                break;
            case 'year':
                $query->whereBetween('completed_at', [
                    Carbon::now()->startOfYear(),
                    Carbon::now()->endOfYear()
                ]);
                break;
        }
        
        return $query->selectRaw('type, SUM(actual_duration) as total_duration, COUNT(*) as count')
            ->groupBy('type')
            ->get()
            ->map(function ($item) {
                return [
                    'type' => $item->type,
                    'type_label' => $this->getTypeLabel($item->type),
                    'minutes' => $item->total_duration ?? 0,
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }
    
    /**
     * Получить серию практики (дни подряд)
     */
    private function getPracticeStreak(int $userId): array
    {
        $currentStreak = 0;
        $longestStreak = 0;
        $tempStreak = 0;
        
        // Получаем все завершенные сессии за последний год
        $sessions = Session::where('user_id', $userId)
            ->where('status', Session::STATUS_COMPLETED)
            ->where('started_at', '>=', Carbon::now()->subYear())
            ->orderBy('started_at', 'desc')
            ->get();
            
        $practiceDays = $sessions->map(function ($session) {
            return $session->started_at->format('Y-m-d');
        })->unique()->sort()->values();
        
        // Подсчитываем текущую серию
        $today = Carbon::today();
        $checkDate = $today->copy();
        
        while ($practiceDays->contains($checkDate->format('Y-m-d'))) {
            $currentStreak++;
            $checkDate->subDay();
        }
        
        // Подсчитываем самую длинную серию
        $tempStreak = 0;
        $lastDate = null;
        
        foreach ($practiceDays as $date) {
            $currentDate = Carbon::parse($date);
            
            if ($lastDate && $currentDate->diffInDays($lastDate) === 1) {
                $tempStreak++;
            } else {
                $tempStreak = 1;
            }
            
            $longestStreak = max($longestStreak, $tempStreak);
            $lastDate = $currentDate;
        }
        
        return [
            'current_streak' => $currentStreak,
            'longest_streak' => $longestStreak,
            'total_practice_days' => $practiceDays->count(),
        ];
    }
    
    /**
     * Получить читаемое название типа упражнения
     */
    private function getTypeLabel(string $type): string
    {
        $labels = [
            'warmup' => 'Разминка',
            'technique' => 'Техника',
            'repertoire' => 'Репертуар',
            'improvisation' => 'Импровизация',
            'sight_reading' => 'Чтение с листа',
            'theory' => 'Теория',
            'break' => 'Перерыв',
            'custom' => 'Пользовательский',
        ];
        
        return $labels[$type] ?? $type;
    }
}