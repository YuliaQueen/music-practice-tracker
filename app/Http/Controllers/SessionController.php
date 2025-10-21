<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Planning\Models\Exercise;
use App\Domains\Planning\Models\Session;
use App\Domains\Planning\Models\SessionBlock;
use App\Domains\Planning\Models\Template;
use App\DTOs\Sessions\CreateSessionDTO;
use App\DTOs\Sessions\UpdateSessionBlockDTO;
use App\Http\Requests\Session\StoreSessionRequest;
use App\Http\Requests\Session\UpdateSessionBlockRequest;
use App\Services\GoalProgressService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SessionController extends Controller
{
    /**
     * Показать список сессий пользователя
     */
    public function index(): Response
    {
        $sessions = Session::forUser(auth()->id())
            ->with(['template', 'blocks'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Sessions/Index', [
            'sessions' => $sessions,
        ]);
    }

    /**
     * Показать форму создания сессии
     */
    public function create(Request $request): Response
    {
        $templates = Template::availableFor(auth()->id())
            ->with('blocks')
            ->orderBy('name')
            ->get();

        // Получаем упражнения из всех сессий пользователя (включая незавершенные)
        $previousExercises = SessionBlock::whereHas('session', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->select('title', 'description', 'type', 'planned_duration')
            ->distinct()
            ->orderBy('title')
            ->get()
            ->groupBy('title')
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'title' => $first->title,
                    'description' => $first->description,
                    'type' => $first->type,
                    'duration' => $first->planned_duration,
                    'usage_count' => $group->count(),
                ];
            })
            ->values();

        // Если нет упражнений из сессий, добавляем упражнения из библиотеки упражнений
        if ($previousExercises->isEmpty()) {
            $exerciseLibrary = Exercise::where('user_id', auth()->id())
                ->select('title', 'description', 'type', 'planned_duration')
                ->get()
                ->map(function ($exercise) {
                    return [
                        'title' => $exercise->title,
                        'description' => $exercise->description,
                        'type' => $exercise->type,
                        'duration' => $exercise->planned_duration,
                        'usage_count' => 0, // Упражнения из библиотеки еще не использовались
                    ];
                });
            
            $previousExercises = $previousExercises->concat($exerciseLibrary);
        }

        // Получаем данные об упражнении, если они переданы
        $exerciseData = null;
        if ($request->has('exercise_id')) {
            $exerciseData = [
                'id' => $request->get('exercise_id'),
                'title' => $request->get('exercise_title'),
                'type' => $request->get('exercise_type'),
                'duration' => $request->get('exercise_duration'),
                'description' => $request->get('exercise_description'),
            ];
        }

        return Inertia::render('Sessions/Create', [
            'templates' => $templates,
            'previousExercises' => $previousExercises,
            'exerciseData' => $exerciseData,
        ]);
    }

    /**
     * Сохранить новую сессию
     */
    public function store(StoreSessionRequest $request): RedirectResponse
    {
        $dto = CreateSessionDTO::fromRequest($request);

        $session = Session::create([
            'user_id' => auth()->id(),
            'practice_template_id' => $dto->template_id,
            ...$dto->toArray(),
            'planned_duration' => array_sum(array_column($dto->getBlocksArray(), 'planned_duration')),
            'status' => Session::STATUS_PLANNED,
        ]);

        // Создаем блоки сессии и упражнения
        foreach ($dto->blocks as $index => $blockDTO) {
            $blockData = $blockDTO->toArray();

            // Создаем блок сессии
            $session->blocks()->create([
                ...$blockData,
                'status' => SessionBlock::STATUS_PLANNED,
                'sort_order' => $index + 1,
            ]);

            // Проверяем, существует ли уже такое упражнение у пользователя
            $existingExercise = Exercise::where('user_id', auth()->id())
                ->where('title', $blockDTO->title)
                ->where('type', $blockDTO->type)
                ->first();

            // Если упражнение не существует, создаем его
            if (!$existingExercise) {
                Exercise::create([
                    'user_id' => auth()->id(),
                    'title' => $blockDTO->title,
                    'description' => $blockDTO->description,
                    'type' => $blockDTO->type,
                    'planned_duration' => $blockDTO->duration,
                    'status' => Exercise::STATUS_PLANNED,
                ]);
            }
        }

        return redirect()->route('sessions.show', $session)
            ->with('success', 'Сессия создана успешно!');
    }

    /**
     * Показать сессию
     */
    public function show(Session $session): Response
    {
        $this->authorize('view', $session);

        $session->load(['blocks', 'template']);

        return Inertia::render('Sessions/Show', [
            'session' => $session,
        ]);
    }

    /**
     * Начать сессию
     */
    public function start(Session $session): RedirectResponse
    {
        $this->authorize('update', $session);

        if (!$session->canBeStarted()) {
            return back()->with('error', 'Сессия не может быть запущена в текущем статусе');
        }

        $session->update([
            'status' => Session::STATUS_ACTIVE,
            'started_at' => now(),
        ]);

        return back()->with('success', 'Сессия запущена!');
    }

    /**
     * Приостановить сессию
     */
    public function pause(Session $session): RedirectResponse
    {
        $this->authorize('update', $session);

        if ($session->status !== Session::STATUS_ACTIVE) {
            return back()->with('error', 'Можно приостановить только активную сессию');
        }

        $session->update([
            'status' => Session::STATUS_PAUSED,
        ]);

        return back()->with('success', 'Сессия приостановлена');
    }

    /**
     * Завершить сессию
     */
    public function complete(Session $session): RedirectResponse
    {
        $this->authorize('update', $session);

        if (!$session->canBeCompleted()) {
            return back()->with('error', 'Сессия не может быть завершена в текущем статусе');
        }

        $actualDuration = $session->getTotalBlocksActualDuration();

        $session->update([
            'status' => Session::STATUS_COMPLETED,
            'completed_at' => now(),
            'actual_duration' => $actualDuration,
        ]);

        // Обновляем прогресс целей после завершения сессии
        $goalProgressService = app(GoalProgressService::class);
        $updatedGoals = $goalProgressService->updateProgressAfterSession($session);
        $completedGoals = $goalProgressService->checkAndCompleteGoals($session->user);

        $message = 'Сессия завершена!';
        if (!empty($completedGoals)) {
            $goalTitles = collect($completedGoals)->pluck('title')->join(', ');
            $message .= " Поздравляем! Достигнуты цели: {$goalTitles}";
        }

        return back()->with('success', $message);
    }

    /**
     * Обновить блок сессии
     */
    public function updateBlock(UpdateSessionBlockRequest $request, Session $session, SessionBlock $block): RedirectResponse
    {
        $this->authorize('update', $session);

        $dto = UpdateSessionBlockDTO::fromRequest($request);
        $updateData = $dto->toArray();

        // Если блок завершается, устанавливаем время завершения если не передано
        if ($dto->status === SessionBlock::STATUS_COMPLETED && $dto->completed_at === null) {
            $updateData['completed_at'] = now();
        }

        $block->update($updateData);

        // Обновляем прогресс целей после обновления блока сессии
        if ($dto->status === SessionBlock::STATUS_COMPLETED) {
            $goalProgressService = app(GoalProgressService::class);
            $updatedGoals = $goalProgressService->updateProgressAfterSessionBlock($block);
            $completedGoals = $goalProgressService->checkAndCompleteGoals($session->user);

            $message = 'Блок обновлен';
            if (!empty($completedGoals)) {
                $goalTitles = collect($completedGoals)->pluck('title')->join(', ');
                $message .= "! Поздравляем! Достигнуты цели: {$goalTitles}";
            }

            return back()->with('success', $message);
        }

        return back()->with('success', 'Блок обновлен');
    }

    /**
     * Удалить сессию
     */
    public function destroy(Session $session): RedirectResponse
    {
        $this->authorize('delete', $session);

        // Удаляем все блоки сессии
        $session->blocks()->delete();
        
        // Удаляем саму сессию
        $session->delete();

        return redirect()->route('sessions.index')
            ->with('success', 'Сессия успешно удалена');
    }
}