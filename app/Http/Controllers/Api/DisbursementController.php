<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDisbursementRequest;
use App\Http\Requests\UpdateDisbursementRequest;
use App\Http\Resources\DisbursementResource;
use App\Models\Award;
use App\Models\Disbursement;
use App\Models\PlannedDisbursement;
use Illuminate\Http\Request;

class DisbursementController extends Controller
{
    public function index(Request $request, Award $award)
    {
        // Check if user is authorized to view disbursements for this award
        if ($award->student_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        $disbursements = $award->plannedDisbursements()
            ->with(['costCategory', 'disbursements'])
            ->get()
            ->flatMap(function ($plannedDisbursement) {
                return $plannedDisbursement->disbursements;
            });

        return DisbursementResource::collection($disbursements);
    }

    public function store(StoreDisbursementRequest $request)
    {
        $plannedDisbursement = PlannedDisbursement::findOrFail($request->planned_disbursement_id);
        
        // Check authorization
        $award = $plannedDisbursement->award;
        if ($award->student_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        // Check if amount exceeds remaining amount
        if ($request->amount > $plannedDisbursement->remaining_amount) {
            abort(400, 'Disbursement amount exceeds remaining amount');
        }

        $disbursement = Disbursement::create([
            'planned_disbursement_id' => $request->planned_disbursement_id,
            'amount' => $request->amount,
            'date' => $request->date,
            'notes' => $request->notes,
            'status' => 'pending',
        ]);

        return new DisbursementResource($disbursement->load('plannedDisbursement.costCategory'));
    }

    public function update(UpdateDisbursementRequest $request, Disbursement $disbursement)
    {
        $plannedDisbursement = $disbursement->plannedDisbursement;
        $award = $plannedDisbursement->award;
        
        // Check authorization
        if ($award->student_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        $oldAmount = $disbursement->amount;
        $oldStatus = $disbursement->status;
        
        $disbursement->update($request->validated());

        // Update remaining amount if amount changed or status changed
        if ($request->has('amount') || $request->has('status')) {
            $amountDiff = $disbursement->amount - $oldAmount;
            
            // If status changed from completed to pending, add back the amount
            if ($oldStatus === 'completed' && $disbursement->status === 'pending') {
                $plannedDisbursement->increment('remaining_amount', $oldAmount);
            }
            // If status changed from pending to completed, deduct the amount
            elseif ($oldStatus === 'pending' && $disbursement->status === 'completed') {
                $plannedDisbursement->decrement('remaining_amount', $disbursement->amount);
            }
            // If amount changed while status is completed
            elseif ($disbursement->status === 'completed' && $amountDiff != 0) {
                $plannedDisbursement->decrement('remaining_amount', $amountDiff);
            }
        }

        return new DisbursementResource($disbursement->load('plannedDisbursement.costCategory'));
    }

    public function destroy(Disbursement $disbursement)
    {
        $plannedDisbursement = $disbursement->plannedDisbursement;
        $award = $plannedDisbursement->award;
        
        // Check authorization
        if ($award->student_id !== request()->user()->id && !request()->user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        // If disbursement was completed, add back the amount
        if ($disbursement->status === 'completed') {
            $plannedDisbursement->increment('remaining_amount', $disbursement->amount);
        }

        $disbursement->delete();

        return response()->noContent();
    }
}

