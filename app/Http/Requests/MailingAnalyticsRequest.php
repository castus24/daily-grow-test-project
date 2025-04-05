<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MailingAnalyticsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.required' => 'Поле "Начальная дата" обязательно для заполнения',
            'start_date.date' => 'Поле "Начальная дата" должно быть датой',
            'end_date.required' => 'Поле "Конечная дата" обязательно для заполнения',
            'end_date.date' => 'Поле "Конечная дата" должно быть датой',
            'end_date.after_or_equal' => 'Конечная дата должна быть больше или равна начальной дате'
        ];
    }
} 