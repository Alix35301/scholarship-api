<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScholarshipBudgetRequest;
use App\Http\Resources\ScholarshipBudgetResource;
use App\Models\Scholarship;
use App\Models\ScholarshipBudget;
use Illuminate\Http\Request;

class ScholarshipBudgetController extends Controller
{
    public function index(Request $request, Scholarship $scholarship)
    {
        $query = $scholarship->budgets()->with('costCategory');

        return ScholarshipBudgetResource::collection($query->latest()->paginate());
    }

    public function store(ScholarshipBudgetRequest $request, Scholarship $scholarship)
    {
        $budget = $scholarship->budgets()->create($request->validated());
        $budget->load('costCategory');

        return new ScholarshipBudgetResource($budget);
    }

    public function show(Scholarship $scholarship, ScholarshipBudget $budget)
    {
        $budget->load('costCategory');

        return new ScholarshipBudgetResource($budget);
    }

    public function update(ScholarshipBudgetRequest $request, Scholarship $scholarship, ScholarshipBudget $budget)
    {
        $budget->update($request->validated());
        $budget->load('costCategory');

        return new ScholarshipBudgetResource($budget);
    }

    public function destroy(Scholarship $scholarship, ScholarshipBudget $budget)
    {
        $budget->delete();

        return response()->noContent();
    }
}

