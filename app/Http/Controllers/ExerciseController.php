<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Planning\Models\Exercise;
use App\DTOs\Exercises\CreateExerciseDTO;
use App\DTOs\Exercises\UpdateExerciseDTO;
use App\Http\Requests\Exercise\StoreExerciseRequest;
use App\Http\Requests\Exercise\UpdateExerciseRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ExerciseController extends Controller
{
    /**
     * Показать список упражнений пользователя
     */
    public function index(): Response
    {
        $exercises = Exercise::forUser(auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Exercises/Index', [
            'exercises' => $exercises,
        ]);
    }

    /**
     * Показать форму создания упражнения
     */
    public function create(): Response
    {
        return Inertia::render('Exercises/Create');
    }

    /**
     * Сохранить новое упражнение
     */
    public function store(StoreExerciseRequest $request): RedirectResponse
    {
        $dto = CreateExerciseDTO::fromRequest($request);

        Exercise::create([
            'user_id' => auth()->id(),
            ...$dto->toArray(),
            'status' => Exercise::STATUS_PLANNED,
        ]);

        return redirect()->route('exercises.index')
            ->with('success', 'Упражнение успешно создано');
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

        $exercise->update($dto->toArray());

        return redirect()->route('exercises.index')
            ->with('success', 'Упражнение успешно обновлено');
    }

    /**
     * Удалить упражнение
     */
    public function destroy(Exercise $exercise): RedirectResponse
    {
        $this->authorize('delete', $exercise);

        $exercise->delete();

        return redirect()->route('exercises.index')
            ->with('success', 'Упражнение успешно удалено');
    }

}