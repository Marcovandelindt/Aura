<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJournalTagRequest;
use App\Models\JournalTag;
use Illuminate\Http\JsonResponse;

class JournalTagController extends Controller
{
    public function store(StoreJournalTagRequest $request): JsonResponse
    {
        $tag = JournalTag::create($request->validated());

        return response()->json(['success' => true, 'tag' => $tag]);
    }

    public function update(StoreJournalTagRequest $request, JournalTag $journalTag): JsonResponse
    {
        $journalTag->update($request->validated());

        return response()->json(['success' => true, 'tag' => $journalTag]);
    }

    public function destroy(JournalTag $journalTag): JsonResponse
    {
        $journalTag->delete();

        return response()->json(['success' => true]);
    }
}
