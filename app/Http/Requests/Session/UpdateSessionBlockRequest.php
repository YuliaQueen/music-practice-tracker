<?php

declare(strict_types=1);

namespace App\Http\Requests\Session;

use App\Enums\SessionBlockStatus;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSessionBlockRequest extends FormRequest
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
            'status'       => 'nullable|string|in:' . implode(',', SessionBlockStatus::values()),
            'actual_duration' => 'nullable|integer|min:0',
            'started_at'   => 'nullable|date',
            'completed_at' => 'nullable|date',
            'notes'        => 'nullable|string',
            'planned_duration' => 'nullable|integer|min:1',
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
            'status.in' => 'Недопустимый статус блока',
            'actual_duration.min' => 'Фактическая длительность не может быть отрицательной',
            'planned_duration.min' => 'Запланированная длительность должна быть не менее 1 минуты',
        ];
    }
}
