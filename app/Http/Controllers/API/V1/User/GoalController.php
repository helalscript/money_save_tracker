<?php

namespace App\Http\Controllers\API\V1\User;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\V1\GoalResource;
use App\Services\API\V1\User\GoalService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GoalController extends Controller
{
    protected $goalService;

    public function __construct(GoalService $goalService)
    {
        $this->goalService = $goalService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $goals = $this->goalService->index($request);

            return Helper::jsonResponse(true, 'Goals fetched successfully', 200, $goals, true);
        } catch (Exception $e) {
            Log::error('GoalController::index' . $e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validateData = $request->validate([
            'monthly_income' => 'nullable|numeric|min:0',
            'fixed_expense' => 'nullable|numeric|min:0',
            'alredy_saved' => 'nullable|numeric|min:0',
            'debt_blance' => 'nullable|numeric|min:0',
            'goal_type' => 'required|string|in:save_money,paying_debt',
            'target_amount' => 'required|numeric|min:0',
            // 'start_date' => 'required|date|date_format:Y-m-d|before:end_date|after_or_equal:today',
            'end_date' => 'required|date|date_format:Y-m-d|after:today',
            // 'daily_target' => 'nullable|numeric|min:0',
            // 'status' => 'nullable|string|in:active,inactive,completed',
        ]);
        // dd($validateData);
        try {
            $goal = $this->goalService->store($validateData);

            return Helper::jsonResponse(true, 'Goal created successfully', 200, $goal);
        } catch (Exception $e) {
            Log::error('GoalController::store' . $e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $goal = $this->goalService->show($id);

            return Helper::jsonResponse(true, 'Goal fetched successfully', 200, $goal);
        } catch (Exception $e) {
            Log::error('GoalController::show' . $e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validateData = $request->validate([
            'monthly_income' => 'nullable|numeric|min:0',
            'fixed_expense' => 'nullable|numeric|min:0',
            'alredy_saved' => 'nullable|numeric|min:0',
            'debt_blance' => 'nullable|numeric|min:0',
            'goal_type' => 'required|string|in:save_money,paying_debt',
            'target_amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'daily_target' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:active,inactive,completed',
        ]);

        try {
            $goal = $this->goalService->update($id, $validateData);

            return Helper::jsonResponse(true, 'Goal updated successfully', 200, $goal);
        } catch (Exception $e) {
            Log::error('GoalController::update' . $e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $goal = $this->goalService->destroy($id);
            return Helper::jsonResponse(true, 'Goal deleted successfully', 200, $goal);
        } catch (Exception $e) {
            Log::error('GoalController::destroy' . $e->getMessage());
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    public function getActiveGoal()
    {
        try {
            $goal = $this->goalService->getActiveGoal();

            return Helper::jsonResponse(true, 'Active Goal fetched successfully', 200, GoalResource::make($goal));
        } catch (Exception $e) {
            Log::error('GoalController::getActiveGoal' . $e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }
    public function getWeeklyStreakData()
    {
        try {
            $streakData = $this->goalService->getWeeklyStreakData();

            return Helper::jsonResponse(true, 'Weekly Streak Data fetched successfully', 200, $streakData);
        } catch (Exception $e) {
            Log::error('GoalController::getWeeklyStreakData' . $e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }
}
