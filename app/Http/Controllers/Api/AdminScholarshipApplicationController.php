<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ScholarshipApplicationResource;
use App\Models\ScholarshipApplication;
use Illuminate\Http\Request;

class AdminScholarshipApplicationController extends Controller
{
    public function adminIndex(Request $request)
    {
        $applications = ScholarshipApplication::with(['scholarship', 'student', 'reviewer', 'receipts', 'documents'])
            ->latest()
            ->paginate();

        return ScholarshipApplicationResource::collection($applications);
    }

    public function adminShow(string $id)
    {
        $application = ScholarshipApplication::with(['scholarship', 'student', 'reviewer', 'receipts', 'documents'])
            ->findOrFail($id);

        return new ScholarshipApplicationResource($application);
    }
}

