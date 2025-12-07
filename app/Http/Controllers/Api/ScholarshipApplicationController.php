<?php

namespace App\Http\Controllers\Api;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentUploadRequest;
use App\Http\Requests\ReviewApplicationRequest;
use App\Http\Requests\ScholarshipApplicationRequest;
use App\Http\Resources\ActivityLogResource;
use App\Http\Resources\DocumentResource;
use App\Http\Resources\ScholarshipApplicationResource;
use App\Models\Document;
use App\Models\ScholarshipApplication;
use App\Services\ActivityLogService;
use App\Services\ScholarshipApplicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ScholarshipApplicationController extends Controller
{
    public function __construct(
        private ScholarshipApplicationService $scholarshipApplicationService,
        private ActivityLogService $activityLogService
    ) {
    }

    public function index(Request $request)
    {
        $query = ScholarshipApplication::query();


        $applications = $query->with(['scholarship', 'student', 'reviewer', 'receipts'])
            ->latest()
            ->paginate();

        return ScholarshipApplicationResource::collection($applications);
    }

    public function myApplications(Request $request)
    {
        $applications = ScholarshipApplication::where('student_id', $request->user()->id)
            ->with(['scholarship', 'student', 'reviewer', 'receipts', 'documents'])
            ->latest()
            ->paginate();

        return ScholarshipApplicationResource::collection($applications);
    }

    public function store(ScholarshipApplicationRequest $request)
    {
        $application = $this->scholarshipApplicationService->createApplication(
            $request->validated(),
            $request->user()
        );

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

    public function review(ReviewApplicationRequest $request, string $id)
    {
        $application = ScholarshipApplication::findOrFail($id);
        
        $oldStatus = $application->status;

        $application->update([
            'status' => $request->status,
            'rejection_reason' => $request->rejection_reason,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $application->load(['scholarship', 'student', 'reviewer']);

        $this->activityLogService->log(
            $application,
            $request->status === ApplicationStatus::Approved ? 'Application approved' : 'Application rejected',
            [
                'old_status' => $oldStatus->value,
                'new_status' => $request->status->value,
                'rejection_reason' => $request->rejection_reason,
                'reviewed_by' => $request->user()->id,
            ],
            $request->user()
        );

        return new ScholarshipApplicationResource($application);
    }

    public function uploadDocuments(DocumentUploadRequest $request, ScholarshipApplication $application)
    {
        if ($application->student_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('documents/applications/' . $application->id, $fileName, 'public');

        $document = Document::create([
            'application_id' => $application->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'description' => $request->description,
        ]);

        $document->load('application');

        return new DocumentResource($document);
    }

    public function logs(Request $request, ScholarshipApplication $application)
    {
        if ($application->student_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        $service = new ActivityLogService();
        $logs = $service->getPaginatedLogsForModel($application, $request->get('per_page', 15));

        return ActivityLogResource::collection($logs);
    }
}

