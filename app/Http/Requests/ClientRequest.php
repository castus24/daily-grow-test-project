<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientRequest extends FormRequest
{
    /**
     * @return array[]
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('clients')->ignore($this->client),
            ],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Поле "Имя" обязательно для заполнения',
            'name.max' => 'Имя не должно превышать 255 символов',
            'phone.max' => 'Номер телефона не должен превышать 20 символов',
            'phone.unique' => 'Этот номер телефона уже используется',
            'email.email' => 'Введите корректный email адрес',
            'email.max' => 'Email не должен превышать 255 символов',
            'email.unique' => 'Этот email уже используется',
            'birth_date.date' => 'Введите корректную дату',
            'birth_date.before_or_equal' => 'Дата рождения не может быть в будущем',
        ];
    }
}
