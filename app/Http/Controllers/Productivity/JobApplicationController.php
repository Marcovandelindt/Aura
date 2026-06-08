<?php

namespace App\Http\Controllers\Productivity;

use App\Enums\JobApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobApplicationRequest;
use App\Http\Requests\UpdateJobApplicationRequest;
use App\Models\JobApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class JobApplicationController extends Controller
{
    public function index(): View
    {
        $applications = JobApplication::query()
            ->orderByRaw("FIELD(status, 'interviewing', 'offered', 'applied', 'saved', 'rejected')")
            ->orderByDesc('updated_at')
            ->get();

        $statuses = JobApplicationStatus::cases();

        $counts = collect(JobApplicationStatus::cases())->mapWithKeys(
            fn ($status) => [$status->value => $applications->where('status', $status)->count()]
        );

        return view('jobs.index', compact('applications', 'statuses', 'counts'));
    }

    public function create(): View
    {
        $statuses = JobApplicationStatus::cases();

        return view('jobs.create', compact('statuses'));
    }

    public function store(StoreJobApplicationRequest $request): RedirectResponse
    {
        JobApplication::create($request->validated());

        return redirect()->route('jobs.index')->with('success', 'Job application saved.');
    }

    public function edit(JobApplication $jobApplication): View
    {
        $statuses = JobApplicationStatus::cases();

        return view('jobs.edit', compact('jobApplication', 'statuses'));
    }

    public function update(UpdateJobApplicationRequest $request, JobApplication $jobApplication): RedirectResponse
    {
        $jobApplication->update($request->validated());

        return redirect()->route('jobs.index')->with('success', 'Job application updated.');
    }

    public function destroy(JobApplication $jobApplication): RedirectResponse
    {
        $jobApplication->delete();

        return redirect()->route('jobs.index')->with('success', 'Job application removed.');
    }
}
