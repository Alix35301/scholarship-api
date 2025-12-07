<?php

namespace App\Http\Controllers\Api;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ScholarshipReceiptRequest;
use App\Http\Requests\VerifyReceiptRequest;
use App\Http\Resources\ScholarshipReceiptResource;
use App\Models\ScholarshipReceipt;
use Illuminate\Http\Request;

class ScholarshipReceiptController extends Controller
{
    public function index(Request $request)
    {
        $query = ScholarshipReceipt::query();

        if ($request->user()->isStudent()) {
            $query->whereHas('application', function($q) use ($request) {
                $q->where('student_id', $request->user()->id);
            });
        }

        if ($request->has('application_id')) {
            $query->where('application_id', $request->application_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $receipts = $query->with(['application.scholarship', 'application.student', 'verifier'])
            ->latest()
            ->paginate();

        return ScholarshipReceiptResource::collection($receipts);
    }

    public function store(ScholarshipReceiptRequest $request)
    {
        $application = \App\Models\ScholarshipApplication::findOrFail($request->application_id);

        if ($application->student_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        if ($application->status !== ApplicationStatus::Approved) {
            abort(422, 'Application must be approved before submitting receipts');
        }

        $receipt = ScholarshipReceipt::create($request->validated());

        $receipt->load(['application.scholarship', 'application.student']);

        return new ScholarshipReceiptResource($receipt);
    }

    public function show(ScholarshipReceipt $receipt)
    {
        if ($receipt->application->student_id !== request()->user()->id && !request()->user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        $receipt->load(['application.scholarship', 'application.student', 'verifier']);

        return new ScholarshipReceiptResource($receipt);
    }

    public function verify(VerifyReceiptRequest $request, ScholarshipReceipt $receipt)
    {
        $receipt->update([
            'status' => $request->status,
            'rejection_reason' => $request->rejection_reason,
            'verified_by' => $request->user()->id,
            'verified_at' => $request->status === 'verified' ? now() : null,
        ]);

        $receipt->load(['application.scholarship', 'application.student', 'verifier']);

        return new ScholarshipReceiptResource($receipt);
    }
}

