<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAgreementCheckInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'checked_on' => ['required', 'date'],
            'status' => ['required', Rule::in(['respected', 'partially', 'not_respected'])],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
