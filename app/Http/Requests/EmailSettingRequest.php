<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmailSettingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'mail_driver' => 'required|string',
            'mail_host' => 'required|string',
            'mail_port' => 'required|integer',
            'mail_username' => 'required|string',
            'mail_password' => 'required|string',
            'mail_encryption' => 'required|string',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string',
            'is_active' => 'boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'mail_driver.required' => 'Поле "Драйвер почты" обязательно для заполнения',
            'mail_host.required' => 'Поле "SMTP сервер" обязательно для заполнения',
            'mail_port.required' => 'Поле "Порт" обязательно для заполнения',
            'mail_port.integer' => 'Поле "Порт" должно быть числом',
            'mail_username.required' => 'Поле "Имя пользователя" обязательно для заполнения',
            'mail_password.required' => 'Поле "Пароль" обязательно для заполнения',
            'mail_encryption.required' => 'Поле "Шифрование" обязательно для заполнения',
            'mail_from_address.required' => 'Поле "Адрес отправителя" обязательно для заполнения',
            'mail_from_address.email' => 'Поле "Адрес отправителя" должно быть email адресом',
            'mail_from_name.required' => 'Поле "Имя отправителя" обязательно для заполнения'
        ];
    }
} 