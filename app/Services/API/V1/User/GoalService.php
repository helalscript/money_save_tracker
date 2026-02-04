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
        // Treat target_amount as the total spending budget for the goal period.
        $startDate = Carbon::parse($goal->start_date);
        $endDate = Carbon::parse($goal->end_date);

        // Total planned days in the goal period
        $totalDays = $startDate->diffInDays($endDate) + 1;

        // Static planned daily limit (initial)
        $plannedDailyLimit = $totalDays > 0 ? $goal->target_amount / $totalDays : 0;

        // Total actually spent
        $spendingAmount = $goal->checkIns()->sum('amount_spending');

        // Remaining budget (never below zero)
        $remainingBudget = max(0, $goal->target_amount - $spendingAmount);

        // Time progress
        $today = Carbon::today();
        $daysElapsed = max(0, $startDate->diffInDays(min($today, $endDate)) + 1);
        $daysRemaining = max(0, $totalDays - $daysElapsed);

        // Recalculated daily limit based on remaining budget and remaining time
        $dynamicDailyLimit = $daysRemaining > 0 ? $remainingBudget / $daysRemaining : 0;

        // Percentage of budget used (spending-based progress)
        $spendPercentage = $goal->target_amount > 0
            ? ($spendingAmount / $goal->target_amount) * 100
            : 0;

        // Update traffic-light indicator based on budget vs time progress
        $this->updateGoalDeadlineIndicator($goal, $spendPercentage, $totalDays > 0 ? ($daysElapsed / $totalDays) * 100 : 0);

        return [
            // For UI, treat needed_days as how many days are left in the original plan.
            'needed_days' => $daysRemaining,
            'original_end_date' => $endDate->toDateString(),
            // We no longer move the deadline; keep it as the original end date.
            'new_end_date' => $endDate->toDateString(),
            // Savings are no longer the primary metric; keep for backward compatibility as 0.
            'saved_amount' => 0,
            'spending_amount' => $spendingAmount,
            'remaining_amount' => $remainingBudget,
            // Expose the *current* daily limit the user should try to stay under.
            'daily_target' => number_format($dynamicDailyLimit, 2),
            'deadline_indicator' => $goal->deadline_indicator ?? 'green',
            // Keep the key name but now represent percentage of budget used.
            'persantage_progress' => number_format($spendPercentage, 2)
        ];
    }

    public function calculateStreak($goal)
    {
        // Get all checking dates from `checkIns` table
        $checkingDates = $goal->checkIns()
            ->orderBy('check_date', 'desc')
            ->pluck('check_date')
            ->map(fn($date) => Carbon::parse($date)->toDateString())
            ->unique()
            ->values()
            ->toArray();

        // If no check-ins exist, streak is 0
        if (empty($checkingDates)) {
            $goal->update(['current_streak' => 0]);
            return 0;
        }

        $streak = 0;
        $today = Carbon::today()->toDateString();
        $checkDate = Carbon::today();

        // Check if today has a check-in
        $todayHasCheckIn = in_array($today, $checkingDates);

        // If today has no check-in, check from yesterday
        if (!$todayHasCheckIn) {
            $checkDate->subDay();
        }

        // Count consecutive days from checkDate backward
        while (in_array($checkDate->toDateString(), $checkingDates)) {
            $streak++;
            $checkDate->subDay();
        }

        // Goal update with new streak count
        $goal->update(['current_streak' => $streak]);

        return $streak;
    }

    public function updateGoalDeadlineIndicator($goal, $spendPercentage, $timePercentage)
    {
        // Compare how fast the user is spending vs how fast time is passing.
        // If spending progress is comfortably below time progress -> green.
        // Slightly above -> yellow. Significantly above -> red.

        // If we have no timeline information, default to green.
        if ($timePercentage <= 0) {
            $goal->update(['deadline_indicator' => 'green']);
            return;
        }

        // Ratio of budget used vs time elapsed.
        $ratio = $spendPercentage / $timePercentage;

        if ($ratio <= 1.0) {
            // On or under track
            $goal->update(['deadline_indicator' => 'green']);
        } elseif ($ratio <= 1.2) {
            // Up to ~20% ahead of planned pace
            $goal->update(['deadline_indicator' => 'yellow']);
        } else {
            // Spending much faster than time progress
            $goal->update(['deadline_indicator' => 'red']);
        }
    }

    public function getActiveGoal()
    {
        try {
            // $goal = Goal::where([
            //     'user_id' => $this->user->id,
            //     'status' => 'active',
            // ])->with([
            //             'checkIns' => function ($query) {
            //                 $query->orderBy('check_date', 'desc');
            //             }
            //         ])
            //     ->first();
            // if (!$goal) {
            //     throw new Exception('Goal not found or does not belong to you');
            // }
            $goal = $this->nowActiveGoal();
            $goal['current_streak'] = $this->calculateStreak($goal);
            $goal['others_data'] = $this->calculateNewEndDate($goal);
            return $goal;
        } catch (Exception $e) {
            Log::error("GoalService::getActiveGoal" . $e->getMessage());
            throw $e;
        }
    }

    public function getWeeklyStreakData()
    {
        $goal = Goal::where([
            'user_id' => $this->user->id,
            'status' => 'active',
        ])->with([
                    'checkIns' => function ($query) {
                        $query->orderBy('check_date', 'desc');
                    }
                ])
            ->first();
        // Get current streak
        $currentStreak = $this->calculateStreak($goal);

        // Get all check-in dates
        $checkInDates = $goal->checkIns()
            ->pluck('check_date')
            ->map(fn($date) => Carbon::parse($date)->toDateString())
            ->unique()
            ->toArray();

        // Get current week (Mon to Sun)
        $startOfWeek = Carbon::now()->startOfWeek(); // Monday
        $endOfWeek = Carbon::now()->endOfWeek(); // Sunday

        // Prepare weekly data
        $weeklyData = [];
        $currentDate = $startOfWeek->copy();

        for ($i = 0; $i < 7; $i++) {
            $dateString = $currentDate->toDateString();
            $dayName = $currentDate->format('D'); // Mon, Tue, Wed...

            $weeklyData[] = [
                'date' => $dateString,
                'day' => $dayName,
                'day_full' => $currentDate->format('l'), // Monday, Tuesday...
                'is_checked' => in_array($dateString, $checkInDates),
                'is_today' => $currentDate->isToday(),
                'is_future' => $currentDate->isFuture(),
            ];

            $currentDate->addDay();
        }

        return [
            'current_streak' => $currentStreak,
            'streak_text' => $currentStreak . ' day streak!',
            'message' => $currentStreak > 0 ? 'Keep it going!' : 'Start your streak today!',
            'week_range' => $startOfWeek->format('M d') . ' - ' . $endOfWeek->format('M d'),
            'weekly_checkins' => $weeklyData,
        ];
    }

    public function bestDaySaving()
    {
        try {
            $goal = $this->nowActiveGoal();
            // Interpret \"saving\" as how far under the daily limit the user stayed.
            $checkIns = $goal->checkIns;

            if ($checkIns->isEmpty()) {
                return 0;
            }

            $bestMargin = 0;

            foreach ($checkIns as $checkIn) {
                $dailyLimit = (float) $checkIn->target_amount;
                $spent = (float) $checkIn->amount_spending;

                if ($dailyLimit <= 0) {
                    continue;
                }

                $margin = max(0, $dailyLimit - $spent);
                if ($margin > $bestMargin) {
                    $bestMargin = $margin;
                }
            }

            return $bestMargin;
        } catch (Exception $e) {
            Log::error("GoalService::bestDaySaving" . $e->getMessage());
            throw $e;
        }
    }
    public function dailyAverageSaving()
    {
        try {
            $goal = $this->nowActiveGoal();
            $checkIns = $goal->checkIns;

            if ($checkIns->isEmpty()) {
                return 0;
            }

            $totalMargin = 0;
            $daysCount = 0;

            foreach ($checkIns as $checkIn) {
                $dailyLimit = (float) $checkIn->target_amount;
                $spent = (float) $checkIn->amount_spending;

                if ($dailyLimit <= 0) {
                    continue;
                }

                $margin = max(0, $dailyLimit - $spent);
                $totalMargin += $margin;
                $daysCount++;
            }

            if ($daysCount === 0) {
                return 0;
            }

            return $totalMargin / $daysCount;
        } catch (Exception $e) {
            Log::error("GoalService::dailyAverageSaving" . $e->getMessage());
            throw $e;
        }
    }

    public function nowActiveGoal()
    {
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
        return $goal;
    }
}