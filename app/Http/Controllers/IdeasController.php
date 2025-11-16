<?php

namespace App\Http\Controllers;

use App\Models\Idea;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class IdeasController extends Controller
{
    /**
     * Display ideas dashboard
     */
    public function index(Request $request): View
    {
        $filter = $request->get('filter', 'all');
        $category = $request->get('category');
        $priority = $request->get('priority');
        
        $query = Idea::query();
        
        // Apply filters
        switch ($filter) {
            case 'active':
                $query->active();
                break;
            case 'completed':
                $query->completed();
                break;
        }
        
        if ($category) {
            $query->category($category);
        }
        
        if ($priority) {
            $query->priority($priority);
        }
        
        $ideas = $query->orderBy('is_completed', 'asc')
                      ->orderBy('priority', 'desc')
                      ->orderBy('created_at', 'desc')
                      ->get();
        
        // Get stats
        $stats = [
            'total' => Idea::count(),
            'active' => Idea::active()->count(),
            'completed' => Idea::completed()->count(),
            'high_priority' => Idea::active()->priority('high')->count(),
        ];
        
        return view('ideas.index', compact('ideas', 'stats', 'filter', 'category', 'priority'));
    }
    
    /**
     * Store a new idea
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
            'category' => 'required|in:feature,improvement,bug,design,other'
        ]);
        
        Idea::create($validated);
        
        return redirect()->route('ideas.index')
            ->with('success', 'Idea added successfully!');
    }
    
    /**
     * Toggle idea completion status
     */
    public function toggle(Idea $idea): RedirectResponse
    {
        $idea->toggleCompletion();
        
        $message = $idea->is_completed 
            ? 'Idea marked as completed!' 
            : 'Idea marked as active!';
            
        return redirect()->route('ideas.index')
            ->with('success', $message);
    }
    
    /**
     * Update an idea
     */
    public function update(Request $request, Idea $idea): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
            'category' => 'required|in:feature,improvement,bug,design,other'
        ]);
        
        $idea->update($validated);
        
        return redirect()->route('ideas.index')
            ->with('success', 'Idea updated successfully!');
    }
    
    /**
     * Delete an idea
     */
    public function destroy(Idea $idea): RedirectResponse
    {
        $idea->delete();
        
        return redirect()->route('ideas.index')
            ->with('success', 'Idea deleted successfully!');
    }
}
