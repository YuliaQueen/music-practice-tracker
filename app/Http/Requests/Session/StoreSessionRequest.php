<?php

declare(strict_types=1);

namespace App\Http\Requests\Session;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class StoreSessionRequest extends FormRequest
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
        $sessionMode = $this->input('session_mode', 'standard');

        // Для Pomodoro блоки не требуются
        if ($sessionMode === 'pomodoro') {
            return [
                'title'          => 'required|string|max:255',
                'description'    => 'nullable|string',
                'session_mode'   => 'required|string|in:standard,pomodoro',
                'pomodoro_total_minutes' => 'required|integer|min:1',
                'pomodoro_work_duration' => 'required|integer|min:1',
                'pomodoro_short_break'   => 'required|integer|min:0',
                'pomodoro_long_break'    => 'required|integer|min:0',
                'pomodoro_cycles_before_long_break' => 'required|integer|min:1',
            ];
        }

        // Для стандартной сессии блоки обязательны
        return [
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'template_id'    => 'nullable|exists:practice_templates,id',
            'session_mode'   => 'nullable|string|in:standard,pomodoro',
            'auto_advance'   => 'nullable|boolean',
            'blocks'         => 'required|array|min:1',
            'blocks.*.title' => 'required|string|max:255',
            'blocks.*.description' => 'nullable|string',
            'blocks.*.duration' => 'required|integer|min:1',
            'blocks.*.type'  => 'required|string',
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
            'title.required'         => 'Название сессии обязательно',
            'blocks.required'        => 'Необходимо добавить хотя бы один блок упражнения',
            'blocks.min'             => 'Необходимо добавить хотя бы один блок упражнения',
            'blocks.*.title.required' => 'Название упражнения обязательно',
            'blocks.*.duration.required' => 'Длительность упражнения обязательна',
            'blocks.*.duration.min'  => 'Длительность должна быть не менее 1 минуты',
            'blocks.*.type.required' => 'Тип упражнения обязателен',
        ];
    }
}
