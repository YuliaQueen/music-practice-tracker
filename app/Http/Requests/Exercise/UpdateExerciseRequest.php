<?php

declare(strict_types=1);

namespace App\Http\Requests\Exercise;

use App\Enums\ExerciseType;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class UpdateExerciseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Авторизация проверяется в контроллере через Policy
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
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'type'        => ['required', 'string', Rule::enum(ExerciseType::class)],
            'planned_duration' => 'required|integer|min:1|max:480',
            'scheduled_for' => 'nullable|date|after:now',
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
            'title.required'      => 'Название упражнения обязательно',
            'type.required'       => 'Тип упражнения обязателен',
            'type.in'             => 'Недопустимый тип упражнения',
            'planned_duration.required' => 'Длительность упражнения обязательна',
            'planned_duration.min' => 'Длительность должна быть не менее 1 минуты',
            'planned_duration.max' => 'Длительность не должна превышать 8 часов (480 минут)',
            'scheduled_for.after' => 'Дата планирования должна быть в будущем',
        ];
    }
}
