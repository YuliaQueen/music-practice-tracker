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
use App\DTOs\Sessions\UpdateSessionBlockDTO;
use App\Domains\Planning\Models\SessionBlock;
use App\Http\Requests\Session\StoreSessionRequest;
use App\Http\Requests\Session\UpdateSessionBlockRequest;
use App\Domains\Planning\Contracts\SessionRepositoryInterface;

/**
 * Контроллер для управления сессиями практики
 */
class SessionController extends Controller
{
    /**
     * Количество сессий на странице
     */
    private const SESSIONS_PER_PAGE = 10;

    public function __construct(
        private readonly SessionService             $sessionService,
        private readonly SessionRepositoryInterface $sessionRepository
    ) {
    }

    /**
     * Показать список сессий пользователя
     */
    public function index(Request $request): Response
    {
        $filters = [
            'search'   => $request->input('search'),
            'exercise' => $request->input('exercise'),
            'status'   => $request->input('status'),
        ];

        $sessions = $this->sessionRepository->getForUser(
            auth()->id(),
            self::SESSIONS_PER_PAGE,
            $filters
        );

        $exercises = $this->sessionService->getUserExercises(auth()->id());

        return Inertia::render('Sessions/Index', [
            'sessions'  => $sessions,
            'exercises' => $exercises,
            'filters'   => $filters,
        ]);
    }

    /**
     * Показать форму создания сессии
     */
    public function create(Request $request): Response
    {
        $templates = $this->sessionService->getAvailableTemplates(auth()->id());
        $previousExercises = $this->sessionService->getPreviousExercises(auth()->id());

        // Получаем данные об упражнении, если они переданы
        $exerciseData = null;
        if ($request->has('exercise_id')) {
            $exerciseData = [
                'id'          => $request->get('exercise_id'),
                'title'       => $request->get('exercise_title'),
                'type'        => $request->get('exercise_type'),
                'duration'    => $request->get('exercise_duration'),
                'description' => $request->get('exercise_description'),
            ];
        }

        return Inertia::render('Sessions/Create', [
            'templates'         => $templates,
            'previousExercises' => $previousExercises,
            'exerciseData'      => $exerciseData,
        ]);
    }

    /**
     * Сохранить новую сессию
     */
    public function store(StoreSessionRequest $request): RedirectResponse
    {
        $dto = CreateSessionDTO::fromRequest($request);

        $result = $this->sessionService->createSession(auth()->user(), $dto);

        if (!$result['success']) {
            return back()->withInput()->with('error', $result['message']);
        }

        return redirect()->route('sessions.show', $result['session'])
            ->with('success', $result['message']);
    }

    /**
     * Показать сессию
     */
    public function show(Session $session): Response
    {
        $this->authorize('view', $session);

        // Загружаем blocks с их аудио записями (eager loading)
        // $with в SessionBlock работает только при прямой загрузке,
        // поэтому явно указываем audioRecordings здесь
        $session->load([
            'blocks.audioRecordings' => function ($query) {
                $query->orderBy('recorded_at', 'desc');
            },
            'template'
        ]);

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
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Изменить порядок блоков в сессии
     */
    public function reorderBlocks(Request $request, Session $session): RedirectResponse
    {
        $this->authorize('update', $session);

        $request->validate([
            'block_ids'   => 'required|array',
            'block_ids.*' => 'required|integer|exists:practice_session_blocks,id',
        ]);

        $result = $this->sessionService->reorderSessionBlocks($session, $request->input('block_ids'));

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Начать выполнение блока сессии
     */
    public function startBlock(Session $session, SessionBlock $block): RedirectResponse
    {
        $this->authorize('update', $session);

        $result = $this->sessionService->startSessionBlock($session, $block);

        if (!$result['success']) {
            return back()->with('error', $result['message']);
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

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Удалить сессию
     */
    public function destroy(Session $session): RedirectResponse
    {
        $this->authorize('delete', $session);

        $result = $this->sessionService->deleteSession($session);

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return redirect()->route('sessions.index')
            ->with('success', $result['message']);
    }
}
