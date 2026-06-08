<?php

namespace App\Http\Controllers\Social;

use App\Enums\IntelligenceLevel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Social\StoreScambaiterConversationRequest;
use App\Http\Requests\Social\UpdateScambaiterConversationRequest;
use App\Models\ScambaiterConversation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ScambaiterConversationController extends Controller
{
    public function create(): View
    {
        $intelligenceLevels = IntelligenceLevel::cases();

        return view('scambaiter.create', compact('intelligenceLevels'));
    }

    public function store(StoreScambaiterConversationRequest $request): RedirectResponse
    {
        $traits = array_filter(
            array_map('trim', explode(',', $request->string('personality_traits', '')))
        );

        $emailAddresses = array_filter(
            array_map('trim', explode(',', $request->string('scammer_email_addresses', '')))
        );

        $conversation = ScambaiterConversation::create([
            'title' => $request->title,
            'scammer_name' => $request->scammer_name,
            'scammer_email_addresses' => array_values($emailAddresses) ?: null,
            'subject' => $request->subject,
            'occupation' => $request->occupation,
            'intelligence_level' => $request->intelligence_level,
            'personality_traits' => array_values($traits) ?: null,
            'personal_details' => $request->personal_details,
            'writing_style' => $request->writing_style,
            'backstory' => $request->backstory,
        ]);

        return redirect()->route('scambaiter.show', $conversation)
            ->with('success', 'Conversation created.');
    }

    public function edit(ScambaiterConversation $conversation): View
    {
        $intelligenceLevels = IntelligenceLevel::cases();

        return view('scambaiter.edit', compact('conversation', 'intelligenceLevels'));
    }

    public function update(UpdateScambaiterConversationRequest $request, ScambaiterConversation $conversation): RedirectResponse
    {
        $traits = array_filter(
            array_map('trim', explode(',', $request->string('personality_traits', '')))
        );

        $emailAddresses = array_filter(
            array_map('trim', explode(',', $request->string('scammer_email_addresses', '')))
        );

        $conversation->update([
            'title' => $request->title,
            'scammer_name' => $request->scammer_name,
            'scammer_email_addresses' => array_values($emailAddresses) ?: null,
            'subject' => $request->subject,
            'occupation' => $request->occupation,
            'intelligence_level' => $request->intelligence_level,
            'personality_traits' => array_values($traits) ?: null,
            'personal_details' => $request->personal_details,
            'writing_style' => $request->writing_style,
            'backstory' => $request->backstory,
        ]);

        return redirect()->route('scambaiter.show', $conversation)
            ->with('success', 'Conversation updated.');
    }

    public function destroy(ScambaiterConversation $conversation): RedirectResponse
    {
        $conversation->delete();

        return redirect()->route('scambaiter.index')
            ->with('success', 'Conversation deleted.');
    }
}
