<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\RedirectResponse;
use App\Domains\Planning\Models\Exercise;
use App\Domains\Planning\Models\SessionBlock;
use App\Domains\Recording\Models\AudioRecording;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Domains\Recording\Services\AudioRecordingService;
use App\Http\Requests\AudioRecording\StoreAudioRecordingRequest;
use App\Http\Requests\AudioRecording\UpdateAudioRecordingRequest;
use App\Domains\Recording\Contracts\AudioRecordingRepositoryInterface;

class AudioRecordingController extends Controller
{
    public function __construct(
        private readonly AudioRecordingService             $service,
        private readonly AudioRecordingRepositoryInterface $repository
    ) {}

    /**
     * Показать список аудио записей пользователя
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['exercise_id', 'session_block_id', 'quality_rating']);

        $recordings = $this->repository->getForUser(
            auth()->id(),
            20,
            $filters
        );

        return Inertia::render('AudioRecordings/Index', [
            'recordings' => $recordings,
        ]);
    }

    /**
     * Сохранить новую аудио запись
     */
    public function store(StoreAudioRecordingRequest $request): RedirectResponse
    {
        $result = $this->service->createRecording(
            auth()->id(),
            $request->file('audio_file'),
            $request->validated()
        );

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['message']]);
        }

        return back()->with([
            'flash' => [
                'recordingId' => $result['recording']->id,
                'message'     => $result['message'],
            ],
        ]);
    }

    /**
     * Показать аудио запись
     */
    public function show(AudioRecording $audioRecording): Response
    {
        Gate::authorize('view', $audioRecording);

        $audioRecording->load(['exercise', 'sessionBlock.session']);

        return Inertia::render('AudioRecordings/Show', [
            'recording' => $audioRecording,
        ]);
    }

    /**
     * Обновить аудио запись
     */
    public function update(UpdateAudioRecordingRequest $request, AudioRecording $audioRecording): RedirectResponse
    {
        Gate::authorize('update', $audioRecording);

        $result = $this->service->updateRecording(
            $audioRecording,
            $request->validated()
        );

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['message']]);
        }

        return back()->with('message', $result['message']);
    }

    /**
     * Удалить аудио запись
     */
    public function destroy(AudioRecording $audioRecording): RedirectResponse
    {
        Gate::authorize('delete', $audioRecording);

        $result = $this->service->deleteRecording($audioRecording);

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['message']]);
        }

        return back()->with('message', $result['message']);
    }

    /**
     * Стриминг аудио файла для воспроизведения
     */
    public function stream(AudioRecording $audioRecording): StreamedResponse
    {
        Gate::authorize('view', $audioRecording);

        $stream = $this->service->getFileStream($audioRecording);

        if ($stream === false) {
            abort(404, 'Файл не найден');
        }

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type'   => $audioRecording->mime_type,
            'Content-Length' => (string)$audioRecording->file_size,
            'Accept-Ranges'  => 'bytes',
            'Cache-Control'  => 'public, max-age=3600',
        ]);
    }

    /**
     * Скачать аудио файл
     */
    public function download(AudioRecording $audioRecording): StreamedResponse
    {
        Gate::authorize('view', $audioRecording);

        if (!$audioRecording->fileExists()) {
            abort(404, 'Файл не найден');
        }

        return \Storage::disk('minio')->download(
            $audioRecording->file_path,
            $audioRecording->file_name
        );
    }

    /**
     * Получить записи для конкретного упражнения
     */
    public function forExercise(Exercise $exercise)
    {
        $this->authorize('view', $exercise);

        $recordings = $this->repository->getForExercise($exercise->id);

        return response()->json(['recordings' => $recordings]);
    }

    /**
     * Получить записи для конкретного блока сессии
     */
    public function forSessionBlock(SessionBlock $sessionBlock)
    {
        $sessionBlock->load('session');
        $this->authorize('view', $sessionBlock->session);

        $recordings = $this->repository->getForSessionBlock($sessionBlock->id);

        return response()->json(['recordings' => $recordings]);
    }
}
