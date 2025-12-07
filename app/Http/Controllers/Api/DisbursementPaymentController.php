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

        // Check if this idempotency key was already used for a different disbursement
        $existingWithKey = Disbursement::where('idempotency_key', $request->idempotency_key)
            ->where('id', '!=', $id)
            ->first();

        if ($existingWithKey) {
            abort(409, 'This idempotency key has already been used for a different disbursement');
        }

        if ($disbursement->status === 'completed') {
            // If same idempotency key, return success (idempotent)
            if ($disbursement->idempotency_key === $request->idempotency_key) {
                return new DisbursementResource($disbursement->load('plannedDisbursement.costCategory'));
            }
            // Different key trying to pay already completed disbursement
            abort(400, 'Disbursement is already completed');
        }

        if ($disbursement->status === 'cancelled') {
            abort(400, 'Cannot pay a cancelled disbursement');
        }

        // Validate no overpayment: Check against remaining scheduled amount
        $remainingScheduled = \App\Models\PaymentSchedule::where('planned_disbursement_id', $disbursement->planned_disbursement_id)
            ->where('status', 'pending')
            ->sum('amount');
        
        if ($disbursement->amount > $remainingScheduled) {
            abort(400, 'Disbursement amount exceeds remaining scheduled amount. Remaining scheduled: ' . number_format($remainingScheduled, 2));
        }

        // Update disbursement status to completed
        $disbursement->update([
            'status' => 'completed',
            'date' => $request->payment_date ?? now(),
            'notes' => $request->notes ?? $disbursement->notes,
            'idempotency_key' => $request->idempotency_key,
        ]);

        // Deduct the amount from planned disbursement remaining amount
        $disbursement->plannedDisbursement->decrement('remaining_amount', $disbursement->amount);

        return new DisbursementResource($disbursement->load('plannedDisbursement.costCategory'));
    }
}

