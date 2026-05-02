<?php

namespace App\Http\Controllers;

use App\Enums\IntelligenceLevel;
use App\Http\Requests\StoreScambaiterConversationRequest;
use App\Http\Requests\UpdateScambaiterConversationRequest;
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

        $conversation = ScambaiterConversation::create([
            'title' => $request->title,
            'scammer_name' => $request->scammer_name,
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

        $conversation->update([
            'title' => $request->title,
            'scammer_name' => $request->scammer_name,
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
