<?php

namespace App\Services\API\V1\User;

use App\Models\Goal;
use DB;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class GoalService
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * Fetch all goals for the authenticated user.
     *
     * @return mixed
     */
    public function index($request)
    {
        try {
            $perPage = $request->per_page ?? 25;

            $goals = Goal::with([
                'checkIns' => function ($query) {
                    $query->orderBy('check_date', 'desc');
                }
            ])
                ->where([
                    'status' => 'active',
                    'user_id' => $this->user->id
                ])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return $goals;
        } catch (Exception $e) {
            Log::error("GoalService::index" . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Store a new goal.
     *
     * @param array $validatedData
     * @return mixed
     */
    public function store(array $validatedData)
    {
        try {
            DB::beginTransaction();
            $validatedData['start_date'] = Carbon::today()->toDateString();
            $totalDays = (new \DateTime($validatedData['end_date']))->diff(new \DateTime($validatedData['start_date']))->days + 1;
            $amountToSave = $validatedData['target_amount'];
            // $amountToSave = $validatedData['target_amount'] - ($validatedData['alredy_saved'] ?? 0);
            $validatedData['daily_target'] = $amountToSave / $totalDays;
            $validatedData['user_id'] = $this->user->id;
            $validatedData['status'] = 'active';

            $goal = Goal::create($validatedData);

            $goal->load([
                'checkIns' => function ($query) {
                    $query->orderBy('check_date', 'desc');
                }
            ]);
            //satus update other goals
            Goal::where('user_id', $this->user->id)
                ->where('id', '!=', $goal->id)
                ->update(['status' => 'inactive']);

            DB::commit();
            return $goal;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("GoalService::store" . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Display a specific goal.
     *
     * @param int $id
     * @return mixed
     */
    public function show(int $id)
    {
        try {
            $goal = Goal::where([
                'id' => $id,
                'user_id' => $this->user->id,
                'status' => 'active',
            ])
                ->with([
                    'checkIns' => function ($query) {
                        $query->orderBy('check_date', 'desc');
                    }
                ])
                ->first();

            if (!$goal) {
                throw new Exception('Goal not found or does not belong to you');
            }
            $goal['current_streak'] = $this->calculateStreak($goal);
            $goal['others_data'] = $this->calculateNewEndDate($goal);
            return $goal;
        } catch (Exception $e) {
            Log::error("GoalService::show" . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a specific goal.
     *
     * @param int $id
     * @param array $validatedData
     * @return mixed
     */
    public function update(int $id, array $validatedData)
    {
        try {
            $goal = Goal::where('id', $id)
                ->where('user_id', $this->user->id)
                ->first();

            if (!$goal) {
                throw new Exception('Goal not found or does not belong to you');
            }

            $goal->update($validatedData);

            $goal->load([
                'checkIns' => function ($query) {
                    $query->orderBy('check_date', 'desc');
                }
            ]);

            return $goal;
        } catch (Exception $e) {
            Log::error("GoalService::update" . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a specific goal.
     *
     * @param int $id
     * @return mixed
     */
    public function destroy(int $id)
    {
        try {
            $goal = Goal::where('id', $id)
                ->where('user_id', $this->user->id)
                ->first();

            if (!$goal) {
                throw new Exception('Goal not found or does not belong to you');
            }

            $goal->delete();

            return $goal;
        } catch (Exception $e) {
            Log::error("GoalService::destroy" . $e->getMessage());
            throw $e;
        }
    }


    public function calculateNewEndDate($goal)
    {
        // Total days between start and end date
        $totalDays = Carbon::parse($goal->start_date)->diffInDays(Carbon::parse($goal->end_date)) + 1;

        // Per day target amount
        $dailyTarget = $goal->target_amount / $totalDays;

        // Total saved amount from checking table
        $savedAmount = $goal->checkIns()->sum('amount_saved');

        // Remaining amount
        $remainingAmount = $goal->target_amount - $savedAmount;
        //persantageProgress
        $persantageProgress = ($savedAmount / $goal->target_amount) * 100;
        // Original end date
        $originalEndDate = Carbon::parse($goal->end_date);

        // Calculate new end date based on remaining amount
        $newEndDate = today()->subDays(1); // Clone to avoid modifying original
        $extraDays = 0;

        if ($remainingAmount > 0) {
            $extraDays = ceil($remainingAmount / $dailyTarget);
            $newEndDate->addDays($extraDays);
        }

        // Goal Status Calculation (Green / Yellow / Red)
        $this->updateGoalDeadlineIndicator($goal, $newEndDate);

        return [
            'needed_days' => isset($extraDays) ? $extraDays : 0,
            'original_end_date' => $originalEndDate->toDateString(),
            'new_end_date' => $newEndDate->toDateString(),
            'saved_amount' => $savedAmount,
            'remaining_amount' => $remainingAmount,
            'daily_target' => number_format($dailyTarget, 2),
            'deadline_indicator' => $goal->deadline_indicator ?? 'green',
            'persantage_progress' => number_format($persantageProgress, 2)
        ];
    }

    public function calculateStreak($goal)
    {
        // Get all checking dates from `checkIns` table with date column name `check_date`
        $checkingDates = $goal->checkIns()
            ->orderBy('check_date', 'desc')
            ->pluck('check_date')
            ->map(fn($date) => Carbon::parse($date)->toDateString())
            ->unique() // Add this to remove duplicate dates
            ->values() // Optional: reset array keys
            ->toArray();

        $streak = 0;
        $today = Carbon::today();

        // Loop through checking dates from today backward
        while (in_array($today->toDateString(), $checkingDates)) {
            $streak++;
            $today->subDay(); // Move to previous day
        }

        // Goal update with new streak count
        $goal->update(['current_streak' => $streak]);

        return $streak;
    }

    public function updateGoalDeadlineIndicator($goal, $newEndDate)
    {
        // dd($newEndDate->toDateString());
        $goalEndDate = $goal->end_date;
        // deadline not crossed yet
        if ($newEndDate->lte($goalEndDate)) {
            $goal->update(['deadline_indicator' => 'green']);
        }
        // deadline crossed
        else {
            $daysOver = $goalEndDate->diffInDays($newEndDate);
            // 1 to 3 day over to yellow
            if ($daysOver >= 1) {
                $goal->update(['deadline_indicator' => 'yellow']);
            } elseif ($daysOver > 3) {
                $goal->update(['deadline_indicator' => 'red']); // more than 3 days over to red
            }
        }
    }

    public function getActiveGoal()
    {
        try {
            $goal = Goal::where([
                'user_id' => $this->user->id,
                'status' => 'active',
            ])->with([
                        'checkIns' => function ($query) {
                            $query->orderBy('check_date', 'desc');
                        }
                    ])
                ->first();
            if (!$goal) {
                throw new Exception('Goal not found or does not belong to you');
            }
            $goal['current_streak'] = $this->calculateStreak($goal);
            $goal['others_data'] = $this->calculateNewEndDate($goal);
            return $goal;
        } catch (Exception $e) {
            Log::error("GoalService::getActiveGoal" . $e->getMessage());
            throw $e;
        }
    }
}