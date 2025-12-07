<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScholarshipRequest;
use App\Http\Resources\ScholarshipResource;
use App\Models\Scholarship;
use Illuminate\Http\Request;

class ScholarshipController extends Controller
{
    public function index(Request $request)
    {
        $query = Scholarship::query();

        if ($request->user()->isStudent()) {
            $query->where('status', 'open');
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $scholarships = $query->with(['applications' => function($q) use ($request) {
            if ($request->user()->isStudent()) {
                $q->where('student_id', $request->user()->id);
            }
        }])->latest()->paginate();

        return ScholarshipResource::collection($scholarships);
    }

    public function store(ScholarshipRequest $request)
    {
        $scholarship = Scholarship::create($request->validated());

        return new ScholarshipResource($scholarship);
    }

    public function show(Scholarship $scholarship)
    {
        $scholarship->load('applications');

        return new ScholarshipResource($scholarship);
    }

    public function update(ScholarshipRequest $request, Scholarship $scholarship)
    {
        $scholarship->update($request->validated());

        return new ScholarshipResource($scholarship);
    }

    public function destroy(Scholarship $scholarship)
    {
        $scholarship->delete();

        return response()->noContent();
    }
}

