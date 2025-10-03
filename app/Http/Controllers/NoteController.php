<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Note;
use App\Domains\Planning\Models\Exercise;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class NoteController extends Controller
{
    /**
     * Показать список нот пользователя
     */
    public function index(): Response
    {
        $notes = Note::forUser(auth()->id())
            ->with('exercise')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $exercises = Exercise::forUser(auth()->id())
            ->orderBy('title')
            ->get(['id', 'title']);

        return Inertia::render('Notes/Index', [
            'notes' => $notes,
            'exercises' => $exercises,
        ]);
    }

    /**
     * Показать форму создания ноты
     */
    public function create(): Response
    {
        $exercises = Exercise::forUser(auth()->id())
            ->orderBy('title')
            ->get(['id', 'title']);

        return Inertia::render('Notes/Create', [
            'exercises' => $exercises,
        ]);
    }

    /**
     * Сохранить новую ноту
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|max:' . (Note::MAX_FILE_SIZE / 1024) . '|mimes:pdf,jpg,jpeg,png,gif,webp,mp3,wav,ogg,mxl,musicxml',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'exercise_id' => 'nullable|exists:exercises,id',
            'is_public' => 'boolean',
        ]);

        $file = $request->file('file');
        $fileHash = hash_file('sha256', $file->getPathname());
        
        // Проверяем, не загружен ли уже такой файл
        $existingNote = Note::where('file_hash', $fileHash)
            ->where('user_id', auth()->id())
            ->first();

        if ($existingNote) {
            return redirect()->back()
                ->with('error', 'Этот файл уже был загружен ранее.');
        }

        // Проверяем MIME тип
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, Note::ALLOWED_MIME_TYPES)) {
            return redirect()->back()
                ->with('error', 'Неподдерживаемый тип файла.');
        }

        // Проверяем, не существует ли уже файл с таким хешем
        $existingNote = Note::where('file_hash', $fileHash)->first();
        if ($existingNote) {
            return redirect()->back()
                ->with('error', 'Файл с таким содержимым уже существует: ' . $existingNote->title);
        }

        // Генерируем уникальное имя файла
        $filename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $uniqueFilename = Str::uuid() . '.' . $extension;
        $filePath = 'notes/' . auth()->id() . '/' . $uniqueFilename;

        // Загружаем файл в MinIO
        try {
            Storage::disk('minio')->put($filePath, file_get_contents($file->getPathname()));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Ошибка при загрузке файла в хранилище: ' . $e->getMessage());
        }

        // Создаем запись в базе данных
        try {
            $note = Note::create([
                'user_id' => auth()->id(),
                'exercise_id' => $request->exercise_id,
                'title' => $request->title,
                'description' => $request->description,
                'filename' => $filename,
                'file_path' => $filePath,
                'mime_type' => $mimeType,
                'file_size' => $file->getSize(),
                'file_hash' => $fileHash,
                'is_public' => $request->boolean('is_public', false),
                'metadata' => [
                    'uploaded_at' => now()->toISOString(),
                    'original_name' => $filename,
                ],
            ]);
        } catch (\Exception $e) {
            // Удаляем файл из хранилища, если не удалось создать запись в БД
            Storage::disk('minio')->delete($filePath);
            return redirect()->back()
                ->with('error', 'Ошибка при сохранении информации о файле: ' . $e->getMessage());
        }

        return redirect()->route('notes.index')
            ->with('success', 'Ноты успешно загружены');
    }

    /**
     * Показать ноту
     */
    public function show(Note $note): Response
    {
        $this->authorize('view', $note);

        $note->load('exercise');

        return Inertia::render('Notes/Show', [
            'note' => $note,
        ]);
    }

    /**
     * Показать форму редактирования ноты
     */
    public function edit(Note $note): Response
    {
        $this->authorize('update', $note);

        $exercises = Exercise::forUser(auth()->id())
            ->orderBy('title')
            ->get(['id', 'title']);

        return Inertia::render('Notes/Edit', [
            'note' => $note,
            'exercises' => $exercises,
        ]);
    }

    /**
     * Обновить ноту
     */
    public function update(Request $request, Note $note): RedirectResponse
    {
        $this->authorize('update', $note);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'exercise_id' => 'nullable|exists:exercises,id',
            'is_public' => 'boolean',
        ]);

        $note->update([
            'title' => $request->title,
            'description' => $request->description,
            'exercise_id' => $request->exercise_id,
            'is_public' => $request->boolean('is_public', false),
        ]);

        return redirect()->route('notes.show', $note)
            ->with('success', 'Ноты успешно обновлены');
    }

    /**
     * Удалить ноту
     */
    public function destroy(Note $note): RedirectResponse
    {
        $this->authorize('delete', $note);

        // Удаляем файл из хранилища
        Storage::disk('minio')->delete($note->file_path);

        // Удаляем запись из базы данных
        $note->delete();

        return redirect()->route('notes.index')
            ->with('success', 'Ноты успешно удалены');
    }

    /**
     * Получить URL для просмотра файла
     */
    public function view(Note $note): JsonResponse
    {
        $this->authorize('view', $note);

        // Используем прокси-URL вместо прямого MinIO URL
        $url = route('notes.proxy', $note->id);

        return response()->json([
            'url' => $url,
            'filename' => $note->filename,
            'mime_type' => $note->mime_type,
        ]);
    }

    /**
     * Прокси для отдачи файлов (обходит проблемы с подписью MinIO)
     */
    public function proxy(Note $note)
    {
        $this->authorize('view', $note);

        try {
            // Получаем файл из MinIO
            $fileContent = Storage::disk('minio')->get($note->file_path);
            
            return response($fileContent)
                ->header('Content-Type', $note->mime_type)
                ->header('Content-Disposition', 'inline; filename="' . $note->filename . '"')
                ->header('Cache-Control', 'public, max-age=3600'); // Кешируем на 1 час
                
        } catch (\Exception $e) {
            abort(404, 'Файл не найден');
        }
    }

    /**
     * Скачать файл
     */
    public function download(Note $note)
    {
        $this->authorize('view', $note);

        // Используем прокси для скачивания
        return $this->proxy($note);
    }

    /**
     * Получить ноты для упражнения
     */
    public function forExercise(Exercise $exercise): JsonResponse
    {
        $this->authorize('view', $exercise);

        $notes = Note::forExercise($exercise->id)
            ->forUser(auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notes);
    }
}
