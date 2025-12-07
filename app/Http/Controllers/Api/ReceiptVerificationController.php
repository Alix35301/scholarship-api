<?php

namespace App\Http\Controllers\Api;

use App\Enums\ReceiptStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyReceiptRequest;
use App\Http\Resources\DisbursementReceiptResource;
use App\Models\DisbursementReceipt;
use App\Services\ActivityLogService;

class ReceiptVerificationController extends Controller
{
    public function __construct(
        private ActivityLogService $activityLogService
    ) {}

    public function verify(VerifyReceiptRequest $request, string $id)
    {
        $receipt = DisbursementReceipt::findOrFail($id);
        
        $oldStatus = $receipt->status;
        $newStatus = ReceiptStatus::from($request->status);

        $receipt->update([
            'status' => $newStatus,
            'rejection_reason' => $request->rejection_reason,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        $receipt->load(['disbursement', 'verifiedBy']);

        $this->activityLogService->log(
            $receipt,
            $newStatus === ReceiptStatus::Approved ? 'Receipt approved' : 'Receipt rejected',
            [
                'old_status' => $oldStatus->value,
                'new_status' => $newStatus->value,
                'rejection_reason' => $request->rejection_reason,
                'verified_by' => $request->user()->id,
            ],
            $request->user()
        );

        return new DisbursementReceiptResource($receipt);
    }
}

