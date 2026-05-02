<?php

namespace App\Models;

use App\Enums\EmailSender;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScambaiterEmail extends Model
{
    protected $fillable = [
        'scambaiter_conversation_id',
        'sender',
        'subject',
        'body',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sender' => EmailSender::class,
            'sent_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ScambaiterConversation::class, 'scambaiter_conversation_id');
    }
}
