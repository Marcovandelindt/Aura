<?php

namespace App\Http\Requests;

use App\Enums\IntelligenceLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreScambaiterConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'scammer_name' => ['nullable', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'intelligence_level' => ['required', new Enum(IntelligenceLevel::class)],
            'personality_traits' => ['nullable', 'string'],
            'personal_details' => ['nullable', 'string'],
            'writing_style' => ['nullable', 'string'],
        ];
    }
}
