<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use App\Services\SessionService;
use Illuminate\Http\RedirectResponse;
use App\DTOs\Sessions\CreateSessionDTO;
use App\Domains\Planning\Models\Session;
use App\Domains\Planning\Models\Exercise;
use App\Domains\Planning\Models\Template;
use App\DTOs\Sessions\UpdateSessionBlockDTO;
use App\Domains\Planning\Models\SessionBlock;
use App\Http\Requests\Session\StoreSessionRequest;
use App\Http\Requests\Session\UpdateSessionBlockRequest;

class SessionController extends Controller
{
    public function __construct(
        private SessionService $sessionService
    ) {
    }

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
                'id'   => $request->get('exercise_id'),
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

        $session = $this->sessionService->createSession(auth()->user(), $dto);

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

        $success = $this->sessionService->startSession($session);

        if (!$success) {
            return back()->with('error', 'Сессия не может быть запущена в текущем статусе');
        }

        return back()->with('success', 'Сессия запущена!');
    }

    /**
     * Приостановить сессию
     */
    public function pause(Session $session): RedirectResponse
    {
        $this->authorize('update', $session);

        $success = $this->sessionService->pauseSession($session);

        if (!$success) {
            return back()->with('error', 'Можно приостановить только активную сессию');
        }

        return back()->with('success', 'Сессия приостановлена');
    }

    /**
     * Завершить сессию
     */
    public function complete(Session $session): RedirectResponse
    {
        $this->authorize('update', $session);

        $result = $this->sessionService->completeSession($session);

        if (!$result['success']) {
            return back()->with('error', $result['message    ']);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Обновить блок сессии
     */
    public function updateBlock(UpdateSessionBlockRequest $request, Session $session, SessionBlock $block): RedirectResponse
    {
        $this->authorize('update', $session);

        $dto = UpdateSessionBlockDTO::fromRequest($request);


        $result = $this->sessionService->updateSessionBlock($session, $block, $dto);

        return back()->with('success', $result['message']);
    }

    /**
     * Удалить сессию
     */
    public function destroy(Session $session): RedirectResponse
    {
        $this->authorize('delete', $session);

        $this->sessionService->deleteSession($session);

        return redirect()->route('sessions.index')
            ->with('success', 'Сессия успешно удалена');
    }
}
