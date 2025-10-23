<?php

declare(strict_types=1);

namespace App\Http\Requests\AudioRecording;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Domains\Recording\Models\AudioRecording;

class StoreAudioRecordingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $maxSize = AudioRecording::MAX_FILE_SIZE / 1024; // Convert to KB
        $extensions = implode(',', AudioRecording::ALLOWED_EXTENSIONS);

        return [
            'audio_file'                => "required|file|mimes:{$extensions}|max:{$maxSize}",
            'exercise_id'               => 'nullable|exists:exercises,id',
            'practice_session_block_id' => 'nullable|exists:practice_session_blocks,id',
            'title'                     => 'nullable|string|max:255',
            'notes'                     => 'nullable|string|max:5000',
            'quality_rating'            => 'nullable|integer|min:1|max:5',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'audio_file.required'                => 'Необходимо загрузить аудио файл',
            'audio_file.file'                    => 'Загруженный файл должен быть файлом',
            'audio_file.mimes'                   => 'Допустимые форматы: ' . implode(', ', AudioRecording::ALLOWED_EXTENSIONS),
            'audio_file.max'                     => 'Максимальный размер файла: 50 МБ',
            'exercise_id.exists'                 => 'Указанное упражнение не найдено',
            'practice_session_block_id.exists'   => 'Указанный блок сессии не найден',
            'title.max'                          => 'Название не должно превышать 255 символов',
            'notes.max'                          => 'Заметки не должны превышать 5000 символов',
            'quality_rating.integer'             => 'Оценка качества должна быть числом',
            'quality_rating.min'                 => 'Оценка качества должна быть от 1 до 5',
            'quality_rating.max'                 => 'Оценка качества должна быть от 1 до 5',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'audio_file'                => 'аудио файл',
            'exercise_id'               => 'упражнение',
            'practice_session_block_id' => 'блок сессии',
            'title'                     => 'название',
            'notes'                     => 'заметки',
            'quality_rating'            => 'оценка качества',
        ];
    }
}
