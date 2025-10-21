<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\PeriodType;
use App\Services\StatisticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StatisticsController extends Controller
{
    public function __construct(
        private readonly StatisticsService $statisticsService
    ) {
    }

    /**
     * Показать страницу статистики
     */
    public function index(Request $request): Response
    {
        $userId = auth()->id();
        $period = PeriodType::fromString($request->get('period', 'week'));

        // Получаем статистику для разных периодов
        $statistics = $this->statisticsService->getAllPeriodStatistics($userId);

        // Получаем данные для графиков
        $chartData = $this->statisticsService->getChartData($userId, $period);

        return Inertia::render('Statistics/Index', [
            'statistics' => $statistics,
            'chartData' => $chartData,
            'currentPeriod' => $period->value,
        ]);
    }
}
