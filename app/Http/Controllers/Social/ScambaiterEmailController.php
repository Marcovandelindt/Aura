<?php

namespace App\Http\Controllers\Social;

use App\Http\Controllers\Controller;
use App\Http\Requests\Social\StoreScambaiterEmailRequest;
use App\Models\ScambaiterConversation;
use App\Models\ScambaiterEmail;
use Illuminate\Http\RedirectResponse;

class ScambaiterEmailController extends Controller
{
    public function store(StoreScambaiterEmailRequest $request, ScambaiterConversation $conversation): RedirectResponse
    {
        $conversation->emails()->create($request->validated());

        if (! $conversation->is_locked) {
            $conversation->update(['is_locked' => true]);
        }

        return redirect()->route('scambaiter.show', $conversation)
            ->with('success', 'Email added.');
    }

    public function destroy(ScambaiterConversation $conversation, ScambaiterEmail $email): RedirectResponse
    {
        $email->delete();

        return redirect()->route('scambaiter.show', $conversation)
            ->with('success', 'Email removed.');
    }
}
