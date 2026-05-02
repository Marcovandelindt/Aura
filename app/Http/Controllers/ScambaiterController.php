<?php

namespace App\Http\Controllers;

use App\Models\ScambaiterConversation;
use App\Models\ScambaiterProfile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class ScambaiterController extends Controller
{
    public function index(): View
    {
        $conversations = ScambaiterConversation::withCount('emails')
            ->orderByDesc('updated_at')
            ->get();

        return view('scambaiter.index', compact('conversations'));
    }

    public function show(ScambaiterConversation $conversation): View
    {
        $conversation->load('emails');

        return view('scambaiter.show', compact('conversation'));
    }

    public function export(ScambaiterConversation $conversation): Response
    {
        $conversation->load('emails');
        $profile = ScambaiterProfile::getInstance();

        $output = $this->buildExport($profile, $conversation);

        return response($output, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="'.str($conversation->title)->slug().'-export.txt"',
        ]);
    }

    public function exportProfile(ScambaiterConversation $conversation): Response
    {
        $profile = ScambaiterProfile::getInstance();
        $output = $this->buildProfileExport($profile, $conversation);

        return response($output, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="'.str($conversation->title)->slug().'-profile-generator.txt"',
        ]);
    }

    private function buildProfileExport(ScambaiterProfile $profile, ScambaiterConversation $conversation): string
    {
        $characterName = $profile->full_name ?: 'Marcus';
        $lines = [];

        $lines[] = '# CHARACTER PROFILE GENERATOR';
        $lines[] = '';
        $lines[] = '## INSTRUCTIONS';
        $lines[] = "Below you will find the raw inputs for a fictional scambaiter character named {$characterName}. Your task is to turn these inputs into a rich, detailed, and internally consistent character profile. The profile should read like a genuine person — with habits, speech patterns, backstory, quirks, and a believable voice. It will be used to write emails in character.";
        $lines[] = '';
        $lines[] = 'Write the profile as a structured document with sections for: background, personality, speech & writing style, recurring details, and any other relevant sections you see fit. Be specific and creative — invent plausible details that are consistent with the inputs below.';
        $lines[] = '';

        $lines[] = '## HOW TO READ THE INPUTS';
        $lines[] = '';
        $lines[] = 'The inputs below use the following formats:';
        $lines[] = '';
        $lines[] = '- Simple fields (Name, Age, etc.) are plain values.';
        $lines[] = '- "Personality traits" is a comma-separated list — treat each as a distinct trait to weave into the character.';
        $lines[] = '- "Intelligence level" uses a fixed scale: Very Dim / Dim / Average / Sharp / Surprisingly Sharp.';
        $lines[] = '- "Personal details" is free text describing recurring people, pets, hobbies, or running jokes to include.';
        $lines[] = '- "Writing style quirks" is free text describing how the character writes emails — their tone, mistakes, formality, etc.';
        $lines[] = '- "Additional facts" is free text with any other biographical details.';
        $lines[] = '';

        $lines[] = '## CHARACTER INPUTS';
        $lines[] = '';

        if ($profile->full_name) {
            $lines[] = "Name: {$profile->full_name}";
        }
        if ($profile->age) {
            $lines[] = "Age: {$profile->age}";
        }
        if ($profile->date_of_birth) {
            $lines[] = "Date of Birth: {$profile->date_of_birth->format('d F Y')}";
        }
        if ($profile->nationality) {
            $lines[] = "Nationality: {$profile->nationality}";
        }
        if ($profile->location) {
            $lines[] = "Location: {$profile->location}";
        }
        if ($conversation->occupation) {
            $lines[] = "Occupation: {$conversation->occupation}";
        }
        $lines[] = "Intelligence level: {$conversation->intelligence_level->label()}";

        if (! empty($conversation->personality_traits)) {
            $lines[] = 'Personality traits: '.implode(', ', $conversation->personality_traits);
        }
        if ($conversation->writing_style) {
            $lines[] = '';
            $lines[] = 'Writing style quirks:';
            $lines[] = $conversation->writing_style;
        }
        if ($conversation->personal_details) {
            $lines[] = '';
            $lines[] = 'Personal details:';
            $lines[] = $conversation->personal_details;
        }
        if ($profile->additional_facts) {
            $lines[] = '';
            $lines[] = 'Additional facts:';
            $lines[] = $profile->additional_facts;
        }
        $lines[] = '';

        $lines[] = '---';
        $lines[] = "Now generate the full character profile for {$characterName}.";

        return implode("\n", $lines);
    }

    private function buildExport(ScambaiterProfile $profile, ScambaiterConversation $conversation): string
    {
        $characterName = $profile->full_name ?: 'Marcus';
        $lines = [];

        $lines[] = '# SCAMBAITER AI PROMPT';
        $lines[] = '';
        $lines[] = '## INSTRUCTIONS';
        $lines[] = "You are helping write the next reply from {$characterName} in an ongoing scambaiter email exchange. Read the character profile and the full email thread below, then write {$characterName}'s next reply in character — matching their writing style, personality, and ongoing storylines exactly. Do not break character. Do not add commentary outside the email itself.";
        $lines[] = '';

        $lines[] = '## GLOBAL CHARACTER PROFILE';
        if ($profile->full_name) {
            $lines[] = "Name: {$profile->full_name}";
        }
        if ($profile->age) {
            $lines[] = "Age: {$profile->age}";
        }
        if ($profile->date_of_birth) {
            $lines[] = "Date of Birth: {$profile->date_of_birth->format('d F Y')}";
        }
        if ($profile->nationality) {
            $lines[] = "Nationality: {$profile->nationality}";
        }
        if ($profile->location) {
            $lines[] = "Location: {$profile->location}";
        }
        if ($profile->additional_facts) {
            $lines[] = '';
            $lines[] = 'Additional facts:';
            $lines[] = $profile->additional_facts;
        }
        $lines[] = '';

        $lines[] = '## CONVERSATION CHARACTER TRAITS';
        if ($conversation->occupation) {
            $lines[] = "Occupation: {$conversation->occupation}";
        }
        $lines[] = "Intelligence level: {$conversation->intelligence_level->label()}";

        if (! empty($conversation->personality_traits)) {
            $lines[] = 'Personality traits: '.implode(', ', $conversation->personality_traits);
        }
        if ($conversation->personal_details) {
            $lines[] = '';
            $lines[] = 'Personal details to weave in:';
            $lines[] = $conversation->personal_details;
        }
        if ($conversation->writing_style) {
            $lines[] = '';
            $lines[] = 'Writing style:';
            $lines[] = $conversation->writing_style;
        }
        $lines[] = '';

        $lines[] = '## EMAIL THREAD';
        foreach ($conversation->emails->sortBy(fn ($e) => $e->sent_at ?? $e->created_at) as $email) {
            $senderLabel = $email->sender->label();
            $lines[] = "--- {$senderLabel} ---";
            if ($email->subject) {
                $lines[] = "Subject: {$email->subject}";
            }
            $lines[] = $email->body;
            $lines[] = '';
        }

        $lines[] = '---';
        $lines[] = "Now write {$characterName}'s next reply.";

        return implode("\n", $lines);
    }
}
