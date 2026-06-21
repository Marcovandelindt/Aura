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
        $header = "🌱 *Inzicht*\n\n";
        $footer = "\n\n_— {$date}_";
        $limit = 4096;

        $firstChunkMax = $limit - mb_strlen($header) - mb_strlen($footer);

        if (mb_strlen($body) <= $firstChunkMax) {
            $this->send($header.$body.$footer);

            return;
        }

        $chunks = $this->splitText($body, $firstChunkMax, $limit);

        foreach ($chunks as $index => $chunk) {
            $text = $index === 0
                ? $header.$chunk.$footer
                : $chunk;

            $this->send($text);
        }
    }

    private function send(string $text): void
    {
        $response = Http::post("https://api.telegram.org/bot{$this->token}/sendMessage", [
            'chat_id' => $this->chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ]);

        if ($response->failed()) {
            Log::warning('Telegram sync failed for insight', ['response' => $response->json()]);
        }
    }

    /** @return string[] */
    private function splitText(string $text, int $firstMax, int $max): array
    {
        $chunks = [];
        $remaining = $text;

        $chunkMax = $firstMax;

        while (mb_strlen($remaining) > 0) {
            if (mb_strlen($remaining) <= $chunkMax) {
                $chunks[] = $remaining;
                break;
            }

            $pos = mb_strrpos(mb_substr($remaining, 0, $chunkMax), "\n");
            $breakAt = $pos !== false ? $pos : $chunkMax;

            $chunks[] = mb_substr($remaining, 0, $breakAt);
            $remaining = ltrim(mb_substr($remaining, $breakAt), "\n");
            $chunkMax = $max;
        }

        return $chunks;
    }
}
