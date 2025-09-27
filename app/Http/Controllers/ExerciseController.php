<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Planning\Models\Exercise;
use Illuminate\Http\Request;
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
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:' . implode(',', Exercise::TYPES),
            'planned_duration' => 'required|integer|min:1|max:480', // максимум 8 часов
            'scheduled_for' => 'nullable|date|after:now',
        ]);

        Exercise::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'planned_duration' => $request->planned_duration,
            'scheduled_for' => $request->scheduled_for,
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
    public function update(Request $request, Exercise $exercise): RedirectResponse
    {
        $this->authorize('update', $exercise);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:' . implode(',', Exercise::TYPES),
            'planned_duration' => 'required|integer|min:1|max:480',
            'scheduled_for' => 'nullable|date|after:now',
        ]);

        $exercise->update($request->only([
            'title',
            'description',
            'type',
            'planned_duration',
            'scheduled_for',
        ]));

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