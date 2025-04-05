<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportClientsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xls,xlsx,csv', 'max:10240'],
            'update_existing' => ['sometimes', 'boolean'],
            'skip_duplicates' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Файл для импорта обязателен',
            'file.file' => 'Неверный формат файла',
            'file.mimes' => 'Поддерживаются только файлы XLS, XLSX и CSV',
            'file.max' => 'Максимальный размер файла - 10MB',
        ];
    }
}
