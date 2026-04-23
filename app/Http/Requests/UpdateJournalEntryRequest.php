<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'date' => ['required', 'date'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:10'],
            'mood_id' => ['nullable', 'exists:moods,id'],
            'location' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'exists:journal_tags,id'],
        ];
    }
}
