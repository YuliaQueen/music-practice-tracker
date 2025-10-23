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
use App\Domains\Recording\Contracts\AudioRecordingRepositoryInterface;

/**
 * Контроллер для управления сессиями практики
 */
class SessionController extends Controller
{
    public function __construct(
        private SessionService $sessionService,
        private SessionRepositoryInterface $sessionRepository,
        private AudioRecordingRepositoryInterface $audioRecordingRepository
    ) {
    }

    /**
     * Показать список сессий пользователя
     */
    public function index(): Response
    {
        $sessions = $this->sessionRepository->getForUser(auth()->id(), 10);

        return Inertia::render('Sessions/Index', [
            'sessions' => $sessions,
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

        $session->load(['blocks', 'template']);

        // Получаем аудио записи для блоков этой сессии
        $blockIds = $session->blocks->pluck('id')->toArray();
        $audioRecordings = collect();

        if (!empty($blockIds)) {
            foreach ($blockIds as $blockId) {
                $recordings = $this->audioRecordingRepository->getForSessionBlock($blockId);
                $audioRecordings = $audioRecordings->concat($recordings);
            }
        }

        return Inertia::render('Sessions/Show', [
            'session'         => $session,
            'audioRecordings' => $audioRecordings->sortByDesc('recorded_at')->values(),
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
