<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PayDisbursementRequest;
use App\Http\Resources\DisbursementResource;
use App\Models\Disbursement;

class DisbursementPaymentController extends Controller
{
    public function pay(PayDisbursementRequest $request, int $id)
    {
        $disbursement = Disbursement::with('plannedDisbursement')->findOrFail($id);

        if ($disbursement->status === 'completed') {
            return new DisbursementResource($disbursement->load('plannedDisbursement.costCategory'));
        }

        if ($disbursement->status === 'cancelled') {
            abort(400, 'Cannot pay a cancelled disbursement');
        }

        // Update disbursement status to completed
        $disbursement->update([
            'status' => 'completed',
            'date' => $request->payment_date ?? now(),
            'notes' => $request->notes ?? $disbursement->notes,
        ]);

        // Deduct the amount from planned disbursement remaining amount
        $disbursement->plannedDisbursement->decrement('remaining_amount', $disbursement->amount);

        return new DisbursementResource($disbursement->load('plannedDisbursement.costCategory'));
    }
}

