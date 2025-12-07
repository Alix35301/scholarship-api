<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentUploadRequest;
use App\Http\Resources\DisbursementReceiptResource;
use App\Models\Disbursement;
use App\Models\DisbursementReceipt;

class DisbursementReceiptController extends Controller
{
    public function store(DocumentUploadRequest $request, Disbursement $disbursement)
    {
        $plannedDisbursement = $disbursement->plannedDisbursement;
        $award = $plannedDisbursement->award;
        
        // Check authorization
        if ($award->student_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('receipts/disbursements/' . $disbursement->id, $fileName, 'public');

        $receipt = DisbursementReceipt::create([
            'disbursement_id' => $disbursement->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'description' => $request->description,
        ]);

        $receipt->load('disbursement');

        return new DisbursementReceiptResource($receipt);
    }
}

