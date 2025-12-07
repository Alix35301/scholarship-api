<?php

namespace App\Http\Controllers\Api;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ScholarshipApplicationResource;
use App\Models\ScholarshipApplication;
use Illuminate\Http\Request;

class ScholarshipAwardController extends Controller
{
    public function index(Request $request)
    {
        $applications = ScholarshipApplication::where('student_id', $request->user()->id)
            ->where('status', ApplicationStatus::Approved)
            ->with(['scholarship', 'student', 'reviewer', 'receipts', 'documents'])
            ->latest()
            ->paginate();

        return ScholarshipApplicationResource::collection($applications);
    }
}

