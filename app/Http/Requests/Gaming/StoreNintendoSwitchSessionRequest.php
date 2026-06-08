<?php

namespace App\Http\Requests\Gaming;

use Illuminate\Foundation\Http\FormRequest;

class StoreNintendoSwitchSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'played_at' => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
        ];
    }
}
