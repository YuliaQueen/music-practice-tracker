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
        return [
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'template_id'    => 'nullable|exists:practice_templates,id',
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
