<?php

namespace App\Http\Requests\Spotify;

use Illuminate\Foundation\Http\FormRequest;

class CreateTopTracksPlaylistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'time_range' => ['sometimes', 'string', 'in:short_term,medium_term,long_term'],
        ];
    }

    public function messages(): array
    {
        return [
            'time_range.in' => 'Time range must be one of: short_term, medium_term, long_term.',
        ];
    }
}
