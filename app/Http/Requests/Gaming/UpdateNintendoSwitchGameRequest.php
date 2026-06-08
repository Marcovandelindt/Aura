<?php

namespace App\Http\Requests\Gaming;

use App\Enums\PlayMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNintendoSwitchGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('nintendo_switch_games', 'name')->ignore($this->route('game'))],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'remove_image' => ['nullable', 'boolean'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'play_mode' => ['nullable', Rule::enum(PlayMode::class)],
            'main_story_completed' => ['nullable', 'boolean'],
            'user_rating' => ['nullable', 'integer', 'min:1', 'max:10'],
            'critic_rating' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
