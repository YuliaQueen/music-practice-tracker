<?php

namespace App\Http\Controllers;

use App\Models\AudioRecording;
use App\Domains\Planning\Models\Exercise;
use App\Domains\Planning\Models\SessionBlock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AudioRecordingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = AudioRecording::where('user_id', auth()->id())
            ->with(['exercise', 'sessionBlock'])
            ->orderBy('recorded_at', 'desc');

        // Фильтрация по упражнению
        if ($request->has('exercise_id')) {
            $query->where('exercise_id', $request->exercise_id);
        }

        // Фильтрация по блоку сессии
        if ($request->has('session_block_id')) {
            $query->where('practice_session_block_id', $request->session_block_id);
        }

        // Фильтрация по оценке качества
        if ($request->has('quality_rating')) {
            $query->where('quality_rating', $request->quality_rating);
        }

        $recordings = $query->paginate(20);

        return Inertia::render('AudioRecordings/Index', [
            'recordings' => $recordings,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'audio_file' => 'required|file|mimes:webm,mp4,m4a,wav,mp3|max:51200', // max 50MB
            'exercise_id' => 'nullable|exists:exercises,id',
            'practice_session_block_id' => 'nullable|exists:practice_session_blocks,id',
            'title' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'quality_rating' => 'nullable|integer|min:1|max:5',
        ]);

        // Проверка: запись должна быть связана либо с упражнением, либо с блоком сессии
        if (empty($validated['exercise_id']) && empty($validated['practice_session_block_id'])) {
            return back()->withErrors(['error' => 'Запись должна быть связана с упражнением или блоком сессии']);
        }

        if (!empty($validated['exercise_id']) && !empty($validated['practice_session_block_id'])) {
            return back()->withErrors(['error' => 'Запись может быть связана только с упражнением ИЛИ блоком сессии']);
        }

        // Проверка прав доступа
        if (!empty($validated['exercise_id'])) {
            $exercise = Exercise::findOrFail($validated['exercise_id']);
            if ($exercise->user_id !== auth()->id()) {
                abort(403, 'Unauthorized');
            }
        }

        if (!empty($validated['practice_session_block_id'])) {
            $sessionBlock = SessionBlock::with('session')->findOrFail($validated['practice_session_block_id']);
            if ($sessionBlock->session->user_id !== auth()->id()) {
                abort(403, 'Unauthorized');
            }
        }

        // Сохраняем файл в MinIO
        $file = $request->file('audio_file');
        $originalFileName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $uniqueFilename = Str::uuid() . '.' . $extension;
        $filePath = 'audio-recordings/' . auth()->id() . '/' . $uniqueFilename;

        try {
            Storage::disk('minio')->put($filePath, file_get_contents($file->getPathname()));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Ошибка при загрузке файла в хранилище: ' . $e->getMessage()]);
        }

        // Создаём запись в БД
        $recording = AudioRecording::create([
            'user_id' => auth()->id(),
            'exercise_id' => $validated['exercise_id'] ?? null,
            'practice_session_block_id' => $validated['practice_session_block_id'] ?? null,
            'title' => $validated['title'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'file_path' => $filePath,
            'file_name' => $originalFileName,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'quality_rating' => $validated['quality_rating'] ?? null,
            'recorded_at' => now(),
        ]);

        return back()->with([
            'flash' => [
                'recordingId' => $recording->id,
                'message' => 'Запись успешно сохранена',
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(AudioRecording $audioRecording)
    {
        Gate::authorize('view', $audioRecording);

        $audioRecording->load(['exercise', 'sessionBlock.session']);

        return Inertia::render('AudioRecordings/Show', [
            'recording' => $audioRecording,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AudioRecording $audioRecording)
    {
        Gate::authorize('update', $audioRecording);

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'quality_rating' => 'nullable|integer|min:1|max:5',
        ]);

        $audioRecording->update($validated);

        return back()->with('message', 'Запись обновлена');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AudioRecording $audioRecording)
    {
        Gate::authorize('delete', $audioRecording);

        // Файл будет удален автоматически через событие модели
        $audioRecording->delete();

        return back()->with('message', 'Запись удалена');
    }

    /**
     * Download the audio file.
     */
    public function download(AudioRecording $audioRecording)
    {
        Gate::authorize('view', $audioRecording);

        if (!Storage::disk('minio')->exists($audioRecording->file_path)) {
            abort(404, 'Файл не найден');
        }

        return Storage::disk('minio')->download($audioRecording->file_path, $audioRecording->file_name);
    }

    /**
     * Get recordings for a specific exercise.
     */
    public function forExercise(Exercise $exercise)
    {
        if ($exercise->user_id !== auth()->id()) {
            abort(403);
        }

        $recordings = $exercise->audioRecordings()
            ->orderBy('recorded_at', 'desc')
            ->get();

        return response()->json(['recordings' => $recordings]);
    }

    /**
     * Get recordings for a specific session block.
     */
    public function forSessionBlock(SessionBlock $sessionBlock)
    {
        $sessionBlock->load('session');

        if ($sessionBlock->session->user_id !== auth()->id()) {
            abort(403);
        }

        $recordings = $sessionBlock->audioRecordings()
            ->orderBy('recorded_at', 'desc')
            ->get();

        return response()->json(['recordings' => $recordings]);
    }
}
