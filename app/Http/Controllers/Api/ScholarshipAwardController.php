<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AwardResource;
use App\Models\Award;
use Illuminate\Http\Request;

class ScholarshipAwardController extends Controller
{
    public function index(Request $request)
    {
        $awards = Award::where('student_id', $request->user()->id)
            ->with(['application.scholarship', 'student', 'plannedDisbursements.costCategory'])
            ->latest()
            ->paginate();

        return AwardResource::collection($awards);
    }

    public function myShow(Request $request, Award $award)
    {
        if ($award->student_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $award->load([
            'application.scholarship', 
            'student', 
            'plannedDisbursements.costCategory',
            'plannedDisbursements.disbursements'
        ]);

        return new AwardResource($award);
    }

    public function adminIndex(Request $request)
    {
        $awards = Award::with(['application.scholarship', 'student', 'plannedDisbursements.costCategory'])
            ->latest()
            ->paginate();

        return AwardResource::collection($awards);
    }

    public function show(Award $award)
    {
        $award->load([
            'application.scholarship', 
            'student', 
            'plannedDisbursements.costCategory',
            'plannedDisbursements.disbursements'
        ]);

        return new AwardResource($award);
    }
}

