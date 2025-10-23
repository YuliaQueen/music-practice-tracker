<?php

declare(strict_types=1);

namespace App\Http\Requests\AudioRecording;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class UpdateAudioRecordingRequest extends FormRequest
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
        return [
            'title'          => 'nullable|string|max:255',
            'notes'          => 'nullable|string|max:5000',
            'quality_rating' => 'nullable|integer|min:1|max:5',
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
            'title.max'              => 'Название не должно превышать 255 символов',
            'notes.max'              => 'Заметки не должны превышать 5000 символов',
            'quality_rating.integer' => 'Оценка качества должна быть числом',
            'quality_rating.min'     => 'Оценка качества должна быть от 1 до 5',
            'quality_rating.max'     => 'Оценка качества должна быть от 1 до 5',
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
            'title'          => 'название',
            'notes'          => 'заметки',
            'quality_rating' => 'оценка качества',
        ];
    }
}
