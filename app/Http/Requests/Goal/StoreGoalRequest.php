<?php

declare(strict_types=1);

namespace App\Http\Requests\Goal;

use App\Domains\Goals\Models\Goal;
use Illuminate\Foundation\Http\FormRequest;

class StoreGoalRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|string|in:' . implode(',', Goal::TYPES),
            'target' => 'required|array',
            'target.value' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
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
            'title.required' => 'Название цели обязательно',
            'type.required' => 'Тип цели обязателен',
            'type.in' => 'Недопустимый тип цели',
            'target.required' => 'Целевое значение обязательно',
            'target.value.required' => 'Укажите целевое значение',
            'target.value.min' => 'Целевое значение должно быть больше 0',
            'start_date.required' => 'Дата начала обязательна',
            'end_date.after' => 'Дата окончания должна быть после даты начала',
        ];
    }
}
