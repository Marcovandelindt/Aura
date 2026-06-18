<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private string $token;

    private string $chatId;

    public function __construct()
    {
        $this->token = config('services.telegram.bot_token');
        $this->chatId = config('services.telegram.chat_id');
    }

    public function sendInsight(string $body, string $date): void
    {
        $text = "🌱 *Inzicht*\n\n{$body}\n\n_— {$date}_";

        $response = Http::post("https://api.telegram.org/bot{$this->token}/sendMessage", [
            'chat_id' => $this->chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ]);

        if ($response->failed()) {
            Log::warning('Telegram sync failed for insight', ['response' => $response->json()]);
        }
    }
}
