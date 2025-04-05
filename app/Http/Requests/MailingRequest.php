<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MailingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'status' => ['required', 'string', 'in:draft,scheduled,sent'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'send_to_all' => ['boolean'],
            'client_ids' => ['required_if:send_to_all,false', 'array'],
            'client_ids.*' => ['exists:clients,id'],
            'send_type' => ['required', 'string', 'in:now,auto,regular'],
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
            'name.required' => 'Название рассылки обязательно для заполнения',
            'name.max' => 'Название рассылки не должно превышать 255 символов',
            'content.required' => 'Содержание рассылки обязательно для заполнения',
            'status.required' => 'Статус рассылки обязателен для заполнения',
            'status.in' => 'Недопустимый статус рассылки',
            'scheduled_at.date' => 'Неверный формат даты планирования',
            'scheduled_at.after' => 'Дата планирования должна быть в будущем',
            'client_ids.required_if' => 'Необходимо указать получателей, если рассылка не для всех',
            'client_ids.*.exists' => 'Указан несуществующий получатель',
            'send_type.required' => 'Тип отправки обязателен для заполнения',
            'send_type.in' => 'Недопустимый тип отправки',
        ];
    }
}
