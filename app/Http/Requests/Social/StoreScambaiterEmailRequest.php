<?php

namespace App\Http\Requests\Social;

use App\Enums\EmailSender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreScambaiterEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sender' => ['required', new Enum(EmailSender::class)],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'sent_at' => ['nullable', 'date'],
        ];
    }
}
