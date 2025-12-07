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
        $query = Scholarship::whereStatus('open')->latest()->paginate();

        return ScholarshipResource::collection($query);
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

