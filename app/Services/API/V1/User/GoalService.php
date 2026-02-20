<?php

namespace App\Services\API\V1\User;

use App\Models\Goal;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
                },
            ])
                ->where([
                    'status' => 'active',
                    'user_id' => $this->user->id,
                ])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return $goals;
        } catch (Exception $e) {
            Log::error('GoalService::index'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Store a new goal.
     *
     * @return mixed
     */
    public function store(array $validatedData)
    {
        try {
            DB::beginTransaction();
            $validatedData['start_date'] = Carbon::today()->toDateString();
            $startDate = Carbon::parse($validatedData['start_date']);
            $endDate = Carbon::parse($validatedData['end_date']);
            $totalDays = $startDate->diffInDays($endDate) + 1;

            // User: monthly income, how much to save (target_amount), and by when.
            // Total spendable = income in period - amount to save. Daily limit = spendable / days.
            $monthlyIncome = (float) ($validatedData['monthly_income'] ?? 0);
            $amountToSave = (float) ($validatedData['target_amount'] ?? 0);

            $validatedData['daily_target'] = intval($totalDays > 0 ? ($monthlyIncome - $amountToSave) / ($totalDays) : 0); // this totaol spending target

            $validatedData['user_id'] = $this->user->id;
            $validatedData['status'] = 'active';

            $goal = Goal::create($validatedData);

            $goal->load([
                'checkIns' => function ($query) {
                    $query->orderBy('check_date', 'desc');
                },
            ]);
            // satus update other goals
            Goal::where('user_id', $this->user->id)
                ->where('id', '!=', $goal->id)
                ->update(['status' => 'inactive']);

            DB::commit();

            return $goal;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('GoalService::store'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Display a specific goal.
     *
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
                    },
                ])
                ->first();

            if (! $goal) {
                throw new Exception('Goal not found or does not belong to you');
            }
            $goal['current_streak'] = $this->calculateStreak($goal);
            $goal['others_data'] = $this->calculateNewEndDate($goal);

            return $goal;
        } catch (Exception $e) {
            Log::error('GoalService::show'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a specific goal.
     *
     * @return mixed
     */
    public function update(int $id, array $validatedData)
    {
        try {
            $goal = Goal::where('id', $id)
                ->where('user_id', $this->user->id)
                ->first();

            if (! $goal) {
                throw new Exception('Goal not found or does not belong to you');
            }

            $goal->update($validatedData);

            $goal->load([
                'checkIns' => function ($query) {
                    $query->orderBy('check_date', 'desc');
                },
            ]);

            return $goal;
        } catch (Exception $e) {
            Log::error('GoalService::update'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a specific goal.
     *
     * @return mixed
     */
    public function destroy(int $id)
    {
        try {
            $goal = Goal::where('id', $id)
                ->where('user_id', $this->user->id)
                ->first();

            if (! $goal) {
                throw new Exception('Goal not found or does not belong to you');
            }

            $goal->delete();

            return $goal;
        } catch (Exception $e) {
            Log::error('GoalService::destroy'.$e->getMessage());
            throw $e;
        }
    }

    public function calculateNewEndDate($goal)
    {
        // New spend-based model:
        // User sets monthly_income, target_amount (how much to save in the period), and end_date.
        // Spendable_total = income_in_period - target_amount
        // Remaining_budget = spendable_total - total_spent
        // Daily_limit_today = remaining_budget / days_remaining
        $startDate = Carbon::parse($goal->start_date)->startOfDay();
        $endDate = Carbon::parse($goal->end_date)->startOfDay();

        $totalDays = max(1, $startDate->diffInDays($endDate) + 1);

        $monthlyIncome = (float) ($goal->monthly_income ?? 0);
        $amountToSave = (float) ($goal->target_amount ?? 0);

        // Approximate income for the goal period using a 30-day month.
        $incomeInPeriod = $monthlyIncome * ($totalDays / 30);
        $totalSpendable = max(0, $incomeInPeriod - $amountToSave);

        $spendingAmount = (float) $goal->checkIns()->sum('amount_spending');
        $remainingBudget = max(0, $totalSpendable - $spendingAmount);

        $today = Carbon::today()->startOfDay();
        $effectiveToday = $today->lte($endDate) ? $today : $endDate;
        $daysElapsed = max(0, $startDate->diffInDays($effectiveToday) + 1);
        $daysRemaining = max(0, $totalDays - $daysElapsed);

        $dynamicDailyLimit = $daysRemaining > 0 ? $remainingBudget / $daysRemaining : 0;

        $spendPercentage = $totalSpendable > 0 ? ($spendingAmount / $totalSpendable) * 100 : 0;
        $timePercentage = $totalDays > 0 ? ($daysElapsed / $totalDays) * 100 : 0;

        $this->updateGoalDeadlineIndicator($goal, $spendPercentage, $timePercentage);

        // IMPORTANT: Use dot-decimal formatting WITHOUT thousands separators
        // so the frontend and other services can safely cast to float.
        $dailyTargetFormatted = number_format($dynamicDailyLimit, 2, '.', '');
        $progressFormatted = number_format($spendPercentage, 2, '.', '');

        return [
            'needed_days' => $daysRemaining,
            'original_end_date' => $endDate->toDateString(),
            'new_end_date' => $endDate->toDateString(),

            // We no longer store/track actual savings in check-ins.
            // So until you introduce a savings field again, saved_amount should be 0.
            'saved_amount' => 0,
            // Expose the user's desired savings for the period separately.
            'save_target' => $amountToSave,

            'spending_amount' => $spendingAmount,
            'remaining_amount' => $remainingBudget,

            // This is the current daily spend limit (dynamic).
            'daily_target' => $dailyTargetFormatted,
            'deadline_indicator' => $goal->deadline_indicator ?? 'green',

            // Keep key name for backward compatibility; now means "% of spendable budget used".
            'persantage_progress' => $progressFormatted,
        ];
    }

    public function calculateStreak($goal)
    {
        // Get all checking dates from `checkIns` table
        $checkingDates = $goal->checkIns()
            ->orderBy('check_date', 'desc')
            ->pluck('check_date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
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
        if (! $todayHasCheckIn) {
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
            Log::error('GoalService::getActiveGoal'.$e->getMessage());
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
            },
        ])
            ->first();
        // Get current streak
        $currentStreak = $this->calculateStreak($goal);

        // Get all check-in dates
        $checkInDates = $goal->checkIns()
            ->pluck('check_date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
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
            'streak_text' => $currentStreak.' day streak!',
            'message' => $currentStreak > 0 ? 'Keep it going!' : 'Start your streak today!',
            'week_range' => $startOfWeek->format('M d').' - '.$endOfWeek->format('M d'),
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
            Log::error('GoalService::bestDaySaving'.$e->getMessage());
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
            Log::error('GoalService::dailyAverageSaving'.$e->getMessage());
            throw $e;
        }
    }

    public function nowActiveGoal()
    {
        $goal = Goal::where([ 'user_id' => $this->user->id,'status' => 'active'])
        ->with([
            'checkIns' => function ($query) {
                $query->orderBy('check_date', 'desc');
            },
        ])
            ->first();
        if (! $goal) {
            throw new Exception('Goal not found or does not belong to you');
        }

        return $goal;
    }
}
