<?php

namespace App\Http\Controllers\API\V1\User;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Services\API\V1\User\CheckInService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckInController extends Controller
{
    protected $checkInService;

    public function __construct(CheckInService $checkInService)
    {
        $this->checkInService = $checkInService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $checkIns = $this->checkInService->index($request);
            return Helper::jsonResponse(true, 'Check-ins fetched successfully', 200, $checkIns);
        } catch (Exception $e) {
            Log::error('CheckInController::index' . $e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Store a new check-in. Backward compatible:
     * - If daily_spent_amount (or amount_spending) provided → system calculates savings (no negative savings).
     * - Else if amount_saved provided → legacy flow.
     * At least one of daily_spent_amount, amount_spending, or amount_saved is required.
     */
    public function store(Request $request)
    {
        $validateData = $request->validate([
            'daily_spent_amount' => 'nullable|numeric|min:0',
            'amount_spending' => 'nullable|numeric|min:0',
            'amount_saved' => 'nullable|numeric|min:0',
            'check_date' => 'nullable|date',
            'spending_type' => 'nullable|string|max:191',
            'spending_name' => 'nullable|string|max:191',
            'note' => 'nullable|string',
        ]);
        if (
            ! array_key_exists('daily_spent_amount', $validateData) &&
            ! array_key_exists('amount_spending', $validateData) &&
            ! array_key_exists('amount_saved', $validateData)
        ) {
            return Helper::jsonErrorResponse('At least one of daily_spent_amount, amount_spending, or amount_saved is required.', 422);
        }

        try {
            $checkIn = $this->checkInService->store($validateData);
            return Helper::jsonResponse(true, 'Check-in created successfully', 200, $checkIn);
        } catch (Exception $e) {
            Log::error('CheckInController::store' . $e->getMessage());
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $checkIn = $this->checkInService->show($id);

            return Helper::jsonResponse(true, 'Check-in fetched successfully', 200, $checkIn);
        } catch (Exception $e) {
            Log::error('CheckInController::show' . $e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validateData = $request->validate([
            'goal_id' => 'nullable|integer|exists:goals,id',
            'daily_spent_amount' => 'nullable|numeric|min:0',
            'amount_spending' => 'nullable|numeric|min:0',
            'amount_saved' => 'nullable|numeric|min:0',
            'target_amount' => 'nullable|numeric|min:0',
            'check_date' => 'nullable|date',
            'status' => 'nullable|string|in:completed,pending,cancelled',
        ]);

        try {
            $checkIn = $this->checkInService->update($id, $validateData);

            return Helper::jsonResponse(true, 'Check-in updated successfully', 200, $checkIn);
        } catch (Exception $e) {
            Log::error('CheckInController::update' . $e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $checkIn = $this->checkInService->destroy($id);
            return Helper::jsonResponse(true, 'Check-in deleted successfully', 200, $checkIn);
        } catch (Exception $e) {
            Log::error('CheckInController::destroy' . $e->getMessage());
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    public function checkInsActivity(Request $request)
    {
        try {
            $checkIns = $this->checkInService->checkInsActivity($request);

            // return Helper::jsonResponse(true, 'Check-ins fetched successfully', 200, $checkIns);
            return $checkIns;
        } catch (Exception $e) {
            Log::error('CheckInController::index' . $e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }
}
