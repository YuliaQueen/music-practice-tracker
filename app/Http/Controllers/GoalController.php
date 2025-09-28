<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Goals\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Inertia\Inertia;
use Inertia\Response;

class GoalController extends Controller
{
    /**
     * Показать список целей пользователя
     */
    public function index(): Response
    {
        $goals = auth()->user()->goals()
            ->orderBy('is_active', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($goal) {
                return [
                    'id' => $goal->id,
                    'title' => $goal->title,
                    'description' => $goal->description,
                    'type' => $goal->type,
                    'type_label' => $goal->getTypeLabel(),
                    'type_icon' => $goal->getTypeIcon(),
                    'type_color' => $goal->getTypeColor(),
                    'target' => $goal->target,
                    'progress' => $goal->progress,
                    'progress_percentage' => $goal->getProgressPercentage(),
                    'remaining' => $goal->getRemaining(),
                    'start_date' => $goal->start_date->format('Y-m-d'),
                    'end_date' => $goal->end_date?->format('Y-m-d'),
                    'is_active' => $goal->is_active,
                    'is_completed' => $goal->is_completed,
                    'completed_at' => $goal->completed_at?->format('Y-m-d H:i'),
                    'created_at' => $goal->created_at->format('Y-m-d H:i'),
                ];
            });

        return Inertia::render('Goals/Index', [
            'goals' => $goals,
            'goalTypes' => Goal::TYPES,
        ]);
    }

    /**
     * Показать форму создания цели
     */
    public function create(): Response
    {
        return Inertia::render('Goals/Create', [
            'goalTypes' => Goal::TYPES,
        ]);
    }

    /**
     * Сохранить новую цель
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|string|in:' . implode(',', Goal::TYPES),
            'target' => 'required|array',
            'target.value' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        $goal = auth()->user()->goals()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'target' => $validated['target'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'is_active' => true,
            'is_completed' => false,
        ]);

        return redirect()->route('goals.index')
            ->with('success', 'Цель успешно создана');
    }

    /**
     * Показать конкретную цель
     */
    public function show(Goal $goal): Response
    {
        $this->authorize('view', $goal);

        return Inertia::render('Goals/Show', [
            'goal' => [
                'id' => $goal->id,
                'title' => $goal->title,
                'description' => $goal->description,
                'type' => $goal->type,
                'type_label' => $goal->getTypeLabel(),
                'type_icon' => $goal->getTypeIcon(),
                'type_color' => $goal->getTypeColor(),
                'target' => $goal->target,
                'progress' => $goal->progress,
                'progress_percentage' => $goal->getProgressPercentage(),
                'remaining' => $goal->getRemaining(),
                'current_value' => $goal->getCurrentValue(),
                'target_value' => $goal->getTargetValue(),
                'start_date' => $goal->start_date->format('Y-m-d'),
                'end_date' => $goal->end_date?->format('Y-m-d'),
                'is_active' => $goal->is_active,
                'is_completed' => $goal->is_completed,
                'completed_at' => $goal->completed_at?->format('Y-m-d H:i'),
                'created_at' => $goal->created_at->format('Y-m-d H:i'),
            ],
        ]);
    }

    /**
     * Показать форму редактирования цели
     */
    public function edit(Goal $goal): Response
    {
        $this->authorize('update', $goal);

        return Inertia::render('Goals/Edit', [
            'goal' => [
                'id' => $goal->id,
                'title' => $goal->title,
                'description' => $goal->description,
                'type' => $goal->type,
                'target' => $goal->target,
                'start_date' => $goal->start_date->format('Y-m-d'),
                'end_date' => $goal->end_date?->format('Y-m-d'),
                'is_active' => $goal->is_active,
            ],
            'goalTypes' => Goal::TYPES,
        ]);
    }

    /**
     * Обновить цель
     */
    public function update(Request $request, Goal $goal): RedirectResponse
    {
        $this->authorize('update', $goal);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|string|in:' . implode(',', Goal::TYPES),
            'target' => 'required|array',
            'target.value' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_active' => 'boolean',
        ]);

        $goal->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'target' => $validated['target'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('goals.index')
            ->with('success', 'Цель успешно обновлена');
    }

    /**
     * Удалить цель
     */
    public function destroy(Goal $goal): RedirectResponse
    {
        $this->authorize('delete', $goal);

        $goal->delete();

        return redirect()->route('goals.index')
            ->with('success', 'Цель успешно удалена');
    }

    /**
     * Обновить прогресс цели
     */
    public function updateProgress(Request $request, Goal $goal): RedirectResponse
    {
        $this->authorize('update', $goal);

        $validated = $request->validate([
            'current' => 'required|integer|min:0',
            'total' => 'nullable|integer|min:1',
        ]);

        $goal->updateProgress($validated['current'], $validated['total'] ?? null);
        $goal->save();

        return redirect()->route('goals.show', $goal)
            ->with('success', 'Прогресс цели обновлен');
    }

    /**
     * Отметить цель как завершенную
     */
    public function complete(Goal $goal): RedirectResponse
    {
        $this->authorize('update', $goal);

        $goal->markAsCompleted();
        $goal->save();

        return redirect()->route('goals.index')
            ->with('success', 'Цель отмечена как завершенная');
    }

    /**
     * Активировать/деактивировать цель
     */
    public function toggle(Goal $goal): RedirectResponse
    {
        $this->authorize('update', $goal);

        $goal->is_active = !$goal->is_active;
        $goal->save();

        $status = $goal->is_active ? 'активирована' : 'деактивирована';

        return redirect()->route('goals.index')
            ->with('success', "Цель {$status}");
    }
}