<?php

namespace App\Http\Requests\Spotify;

use Illuminate\Foundation\Http\FormRequest;

class CreateDateRangePlaylistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date', 'before_or_equal:end_date'],
            'end_date' => ['required', 'date', 'before_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.required' => 'A start date is required.',
            'start_date.before_or_equal' => 'The start date must be before or equal to the end date.',
            'end_date.required' => 'An end date is required.',
            'end_date.before_or_equal' => 'The end date cannot be in the future.',
        ];
    }
}
