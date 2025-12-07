<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewApplicationRequest;
use App\Http\Requests\ScholarshipApplicationRequest;
use App\Http\Resources\ScholarshipApplicationResource;
use App\Models\ScholarshipApplication;
use Illuminate\Http\Request;

class ScholarshipApplicationController extends Controller
{
    public function index(Request $request)
    {
        $query = ScholarshipApplication::query();


        $applications = $query->with(['scholarship', 'student', 'reviewer', 'receipts'])
            ->latest()
            ->paginate();

        return ScholarshipApplicationResource::collection($applications);
    }

    public function store(ScholarshipApplicationRequest $request)
    {
        $application = ScholarshipApplication::create([
            ...$request->validated(),
            'student_id' => $request->user()->id,
            'applied_at' => now(),
        ]);

        $application->load(['scholarship', 'student']);

        return new ScholarshipApplicationResource($application);
    }

    public function show(ScholarshipApplication $application)
    {
        if ($application->student_id !== request()->user()->id && !request()->user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        $application->load(['scholarship', 'student', 'reviewer', 'receipts']);

        return new ScholarshipApplicationResource($application);
    }

    public function review(ReviewApplicationRequest $request, ScholarshipApplication $application)
    {
        $application->update([
            'status' => $request->status,
            'rejection_reason' => $request->rejection_reason,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $application->load(['scholarship', 'student', 'reviewer']);

        return new ScholarshipApplicationResource($application);
    }
}

