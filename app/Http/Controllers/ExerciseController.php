<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Services\ExerciseService;
use App\Domains\Planning\Models\Exercise;
use App\DTOs\Exercises\CreateExerciseDTO;
use App\DTOs\Exercises\UpdateExerciseDTO;
use App\Http\Requests\Exercise\StoreExerciseRequest;
use App\Http\Requests\Exercise\UpdateExerciseRequest;
use App\Domains\Planning\Contracts\ExerciseRepositoryInterface;

/**
 * Контроллер для управления упражнениями
 */
class ExerciseController extends Controller
{
    /**
     * Количество упражнений на странице
     */
    private const EXERCISES_PER_PAGE = 10;

    public function __construct(
        private readonly ExerciseService              $exerciseService,
        private readonly ExerciseRepositoryInterface $exerciseRepository
    ) {
    }

    /**
     * Показать список упражнений пользователя
     */
    public function index(Request $request): Response
    {
        $filters = [
            'search' => $request->input('search'),
            'type'   => $request->input('type'),
        ];

        $exercises = $this->exerciseRepository->getForUser(
            auth()->id(),
            self::EXERCISES_PER_PAGE,
            $filters
        );

        return Inertia::render('Exercises/Index', [
            'exercises' => $exercises,
            'filters'   => $filters,
        ]);
    }

    /**
     * Сохранить новое упражнение
     */
    public function store(StoreExerciseRequest $request): RedirectResponse
    {
        $dto = CreateExerciseDTO::fromRequest($request);

        $result = $this->exerciseService->createExercise(auth()->user(), $dto);

        if (!$result['success']) {
            return back()->withInput()->with('error', $result['message']);
        }

        return redirect()->route('exercises.index')
            ->with('success', $result['message']);
    }

    /**
     * Показать форму создания упражнения
     */
    public function create(): Response
    {
        return Inertia::render('Exercises/Create');
    }

    /**
     * Показать упражнение
     */
    public function show(Exercise $exercise): Response
    {
        $this->authorize('view', $exercise);

        return Inertia::render('Exercises/Show', [
            'exercise' => $exercise,
        ]);
    }

    /**
     * Показать форму редактирования упражнения
     */
    public function edit(Exercise $exercise): Response
    {
        $this->authorize('update', $exercise);

        return Inertia::render('Exercises/Edit', [
            'exercise' => $exercise,
        ]);
    }

    /**
     * Обновить упражнение
     */
    public function update(UpdateExerciseRequest $request, Exercise $exercise): RedirectResponse
    {
        $this->authorize('update', $exercise);

        $dto = UpdateExerciseDTO::fromRequest($request);

        $result = $this->exerciseService->updateExercise($exercise, $dto);

        if (!$result['success']) {
            return back()->withInput()->with('error', $result['message']);
        }

        return redirect()->route('exercises.index')
            ->with('success', $result['message']);
    }

    /**
     * Удалить упражнение
     */
    public function destroy(Exercise $exercise): RedirectResponse
    {
        $this->authorize('delete', $exercise);

        $result = $this->exerciseService->deleteExercise($exercise);

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return redirect()->route('exercises.index')
            ->with('success', $result['message']);
    }

}
