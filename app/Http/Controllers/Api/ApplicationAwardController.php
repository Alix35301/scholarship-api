<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AwardResource;
use App\Models\ScholarshipApplication;
use App\Services\AwardService;
use Illuminate\Http\Request;

class ApplicationAwardController extends Controller
{
    public function __construct(
        private AwardService $awardService
    ) {
    }

    public function store(Request $request, string $id)
    {
        $application = ScholarshipApplication::findOrFail($id);

        try {
            $award = $this->awardService->createAwardFromApplication($application);
            return new AwardResource($award);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }
}

