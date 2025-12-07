<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDisbursementRequest;
use App\Http\Requests\UpdateDisbursementRequest;
use App\Http\Resources\DisbursementResource;
use App\Http\Resources\PlannedDisbursementResource;
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

        $type = $request->query('type', 'all'); // 'all', 'planned', 'actual'

        if ($type === 'planned') {
            // Return only planned disbursements
            $query = PlannedDisbursement::where('award_id', $award->id)
                ->with(['costCategory']);

            // Allow filtering by cost_category_id
            if ($request->has('cost_category_id')) {
                $query->where('cost_category_id', $request->query('cost_category_id'));
            }

            $results = $query->paginate($request->query('per_page', 15));
            return PlannedDisbursementResource::collection($results);
        }

        if ($type === 'actual') {
            // Return only actual disbursements
            $plannedIds = $award->plannedDisbursements()->pluck('id');
            $query = Disbursement::whereIn('planned_disbursement_id', $plannedIds)
                ->with(['plannedDisbursement.costCategory']);

            // Allow filtering by status
            if ($request->has('status')) {
                $query->where('status', $request->query('status'));
            }

            // Allow filtering by cost_category_id through planned disbursements
            if ($request->has('cost_category_id')) {
                $query->whereHas('plannedDisbursement', function ($q) use ($request) {
                    $q->where('cost_category_id', $request->query('cost_category_id'));
                });
            }

            // Allow filtering by date range
            if ($request->has('date_from')) {
                $query->where('date', '>=', $request->query('date_from'));
            }
            if ($request->has('date_to')) {
                $query->where('date', '<=', $request->query('date_to'));
            }

            $results = $query->orderBy('date', 'desc')->paginate($request->query('per_page', 15));
            return DisbursementResource::collection($results);
        }

        // Return all (both planned and actual) - default behavior
        $plannedDisbursements = $award->plannedDisbursements()
            ->with(['costCategory', 'disbursements'])
            ->get();

        return response()->json([
            'data' => [
                'planned' => PlannedDisbursementResource::collection($plannedDisbursements),
                'actual' => DisbursementResource::collection(
                    $plannedDisbursements->flatMap(fn($pd) => $pd->disbursements)
                ),
            ],
        ]);
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

