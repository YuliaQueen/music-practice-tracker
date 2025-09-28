<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\GoalProgressService;
use App\Domains\User\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateGoalProgressCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'goals:update-progress 
                            {--date= : Дата для обновления прогресса (Y-m-d)}
                            {--user= : ID пользователя для обновления прогресса}
                            {--all : Обновить прогресс для всех пользователей}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обновить прогресс целей пользователей на основе их сессий';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $goalProgressService = app(GoalProgressService::class);
        
        $date = $this->option('date') ? Carbon::createFromFormat('Y-m-d', $this->option('date')) : Carbon::now();
        $userId = $this->option('user');
        $allUsers = $this->option('all');
        
        $this->info("Обновление прогресса целей за {$date->format('Y-m-d')}...");
        
        if ($userId) {
            // Обновляем прогресс для конкретного пользователя
            $user = User::find($userId);
            if (!$user) {
                $this->error("Пользователь с ID {$userId} не найден");
                return 1;
            }
            
            $this->updateUserGoals($goalProgressService, $user, $date);
        } elseif ($allUsers) {
            // Обновляем прогресс для всех пользователей
            $users = User::whereHas('goals')->get();
            
            if ($users->isEmpty()) {
                $this->info('Пользователи с целями не найдены');
                return 0;
            }
            
            $this->info("Найдено {$users->count()} пользователей с целями");
            
            $progressBar = $this->output->createProgressBar($users->count());
            $progressBar->start();
            
            foreach ($users as $user) {
                $this->updateUserGoals($goalProgressService, $user, $date);
                $progressBar->advance();
            }
            
            $progressBar->finish();
            $this->newLine();
        } else {
            $this->error('Необходимо указать --user=ID или --all');
            return 1;
        }
        
        $this->info('Обновление прогресса завершено!');
        return 0;
    }
    
    /**
     * Обновить цели для конкретного пользователя
     */
    private function updateUserGoals(GoalProgressService $goalProgressService, User $user, Carbon $date): void
    {
        $dayStart = $date->copy()->startOfDay();
        $dayEnd = $date->copy()->endOfDay();
        
        $updatedGoals = $goalProgressService->updateProgressFromSessions($user, $dayStart, $dayEnd);
        $completedGoals = $goalProgressService->checkAndCompleteGoals($user);
        
        if (!empty($updatedGoals)) {
            $this->line("Пользователь {$user->name}: обновлено " . count($updatedGoals) . " целей");
        }
        
        if (!empty($completedGoals)) {
            $goalTitles = collect($completedGoals)->pluck('title')->join(', ');
            $this->line("Пользователь {$user->name}: достигнуты цели: {$goalTitles}");
        }
    }
}