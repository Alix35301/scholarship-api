<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentUploadRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Disbursement;
use App\Models\Document;

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
        $filePath = $file->storeAs('documents/disbursements/' . $disbursement->id, $fileName, 'public');

        $document = Document::create([
            'disbursement_id' => $disbursement->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'description' => $request->description,
        ]);

        $document->load('disbursement');

        return new DocumentResource($document);
    }
}

