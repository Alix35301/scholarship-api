<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CostCategoryRequest;
use App\Http\Resources\CostCategoryResource;
use App\Models\CostCategory;
use Illuminate\Http\Request;

class CostCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = CostCategory::query();

        return CostCategoryResource::collection($query->latest()->paginate());
    }

    public function store(CostCategoryRequest $request)
    {
        $costCategory = CostCategory::create($request->validated());

        return new CostCategoryResource($costCategory);
    }

    public function show(CostCategory $costCategory)
    {
        return new CostCategoryResource($costCategory);
    }

    public function update(CostCategoryRequest $request, CostCategory $costCategory)
    {
        $costCategory->update($request->validated());

        return new CostCategoryResource($costCategory);
    }

    public function destroy(CostCategory $costCategory)
    {
        $costCategory->delete();

        return response()->noContent();
    }
}
