<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['required', Rule::in(['huis', 'beweging', 'welzijn', 'persoonlijk', 'werk', 'overig'])],
            'difficulty' => ['required', Rule::in(['easy', 'normal', 'hard'])],
            'type' => ['required', Rule::in(['once', 'daily', 'weekly'])],
            'due_date' => ['nullable', 'date'],
        ];
    }
}
