<?php

namespace App\Services\API\V1\User;

use App\Models\CheckIn;
use App\Models\Goal;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckInService
{
    protected $user;

    public function __construct(protected SavingsCalculationService $savingsCalculation)
    {
        $this->user = Auth::user();
    }

    /**
     * Fetch check-ins for the current active goal and return API structure:
     * today (spent, saved if check-in happened), progress, guidance.
     * Days passed = COUNT(check-in records); no calendar assumptions.
     *
     * @return array{today: array, progress: array, guidance: array, check_ins: array, pagination: array}
     */
    public function index($request)
    {
        try {
            $perPage = (int) ($request->per_page ?? 25);
            $activeGoal = Goal::where('user_id', $this->user->id)->where('status', 'active')->first();

            $emptyPagination = [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => $perPage,
                'total' => 0,
                'first_page_url' => $request->url() . '?page=1',
                'last_page_url' => $request->url() . '?page=1',
                'next_page_url' => null,
                'prev_page_url' => null,
                'from' => null,
                'to' => null,
                'path' => $request->path(),
            ];

            if (! $activeGoal) {
                return [
                    'today' => ['spent' => null, 'saved' => null],
                    'progress' => [
                        'total_saved' => 0,
                        'goal_save' => 0,
                        'goal_completion_percent' => 0,
                        'days_passed' => 0,
                        'total_days' => 0,
                    ],
                    'guidance' => [
                        'remaining_days' => 0,
                        'remaining_save' => 0,
                        'next_day_required_save' => null,
                        'next_day_allowed_spend' => null,
                    ],
                    'check_ins' => [],
                    'pagination' => $emptyPagination,
                    // Backward compatibility: keep legacy keys for existing clients.
                    'total_amount_spending' => 0,
                    'total_amount_saved' => 0,
                    'daily_target' => 0,
                ];
            }

            $payload = $this->savingsCalculation->buildProgressPayload($activeGoal);
            $derived = $this->savingsCalculation->getGoalDerived($activeGoal);
            $perDayAllowedSpend = $derived['per_day_allowed_spend'];

            // Today: if multiple check-ins on same day, sum all daily_spent_amount for that date.
            $todayCheckIns = CheckIn::where('goal_id', $activeGoal->id)
                ->whereDate('check_date', Carbon::today())
                ->get();
            if ($todayCheckIns->isNotEmpty()) {
                // Total daily spent = sum of all check-ins' daily_spent_amount for today.
                $totalDailySpent = (float) $todayCheckIns->sum(function ($c) {
                    return (float) ($c->daily_spent_amount ?? $c->amount_spending ?? 0);
                });
                // today_saved = max(0, daily_limit - total_daily_spent)
                $totalDailySave = $this->savingsCalculation->calculateDailySave(
                    $perDayAllowedSpend,
                    $totalDailySpent
                );
                $payload['today'] = ['spent' => round($totalDailySpent, 2), 'saved' => $totalDailySave];
            }

            $checkIns = CheckIn::where('goal_id', $activeGoal->id)
                ->orderBy('check_date', 'desc')
                ->paginate($perPage);

            $payload['check_ins'] = $checkIns->items();
            $payload['pagination'] = [
                'current_page' => $checkIns->currentPage(),
                'last_page' => $checkIns->lastPage(),
                'per_page' => $checkIns->perPage(),
                'total' => $checkIns->total(),
                'first_page_url' => $checkIns->url(1),
                'last_page_url' => $checkIns->url($checkIns->lastPage()),
                'next_page_url' => $checkIns->nextPageUrl(),
                'prev_page_url' => $checkIns->previousPageUrl(),
                'from' => $checkIns->firstItem(),
                'to' => $checkIns->lastItem(),
                'path' => $checkIns->path(),
            ];
            // Backward compatibility: legacy keys for existing clients.
            $derived = $this->savingsCalculation->getGoalDerived($activeGoal);
            $payload['total_amount_spending'] = (int) $checkIns->getCollection()->sum(function ($c) {
                return (float) ($c->daily_spent_amount ?? $c->amount_spending ?? 0);
            });
            $payload['total_amount_saved'] = (int) round($payload['progress']['total_saved']);
            $payload['daily_target'] = (int) round($derived['per_day_allowed_spend']);

            return $payload;
        } catch (Exception $e) {
            Log::error("CheckInService::index" . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Store a new check-in. Savings calculated ONLY when this check-in is created (no row = no calculation).
     * - If daily_spent_amount (or amount_spending) provided → calculate savings; overspending never creates negative savings.
     * - Else if saved_amount (amount_saved) provided → legacy flow; calculated_daily_save = max(0, saved_amount).
     * - check_date from request or now(); no auto-generated calendar days.
     *
     * @param array $validatedData
     * @return CheckIn
     */
    public function store(array $validatedData)
    {
        try {
            $goal = Goal::where('user_id', $this->user->id)->where('status', 'active')->first();
            if (! $goal) {
                throw new Exception('Goal not found or does not belong to you');
            }

            $derived = $this->savingsCalculation->getGoalDerived($goal);
            $perDayAllowedSpend = $derived['per_day_allowed_spend'];

            // Resolve check_date: from request or now().
            $checkDate = isset($validatedData['check_date']) ? Carbon::parse($validatedData['check_date'])->startOfDay() : Carbon::today();

            // Resolve daily spent: primary = daily_spent_amount, legacy = amount_spending.
            $dailySpent = null;
            if (isset($validatedData['daily_spent_amount']) && $validatedData['daily_spent_amount'] !== '' && $validatedData['daily_spent_amount'] !== null) {
                $dailySpent = (float) $validatedData['daily_spent_amount'];
            } elseif (isset($validatedData['amount_spending']) && $validatedData['amount_spending'] !== '' && $validatedData['amount_spending'] !== null) {
                $dailySpent = (float) $validatedData['amount_spending'];
            }

            $calculatedDailySave = null;
            $amountSaved = null;

            if ($dailySpent !== null) {
                // If multiple check-ins on same day: total_daily_spent = sum of all check-ins' daily_spent_amount for that date.
                $existingDailySpent = (float) CheckIn::where('goal_id', $goal->id)
                    ->whereDate('check_date', $checkDate)
                    ->sum('daily_spent_amount');
                $totalDailySpent = $existingDailySpent + $dailySpent;

                // today_saved = max(0, daily_limit - total_daily_spent)
                $calculatedDailySave = $this->savingsCalculation->calculateDailySave(
                    $perDayAllowedSpend,
                    $totalDailySpent
                );

                $amountSaved = $calculatedDailySave; // legacy field for backward compat
            } elseif (array_key_exists('amount_saved', $validatedData) && $validatedData['amount_saved'] !== '' && $validatedData['amount_saved'] !== null) {
                // Legacy: use saved_amount; never store negative.
                $amountSaved = max(0, (float) $validatedData['amount_saved']);
                $calculatedDailySave = $amountSaved;
            }

            $payload = [
                'goal_id' => $goal->id,
                'target_amount' => $goal->daily_target ?? 0,
                'check_date' => $checkDate,
                'status' => 'completed',
            ];
            $payload['amount_spending'] = $dailySpent;
            $payload['amount_saved'] = $amountSaved;
            $payload['daily_spent_amount'] = $dailySpent;
            $payload['calculated_daily_save'] = $calculatedDailySave;
            foreach (['spending_type', 'spending_name', 'note'] as $key) {
                if (array_key_exists($key, $validatedData)) {
                    $payload[$key] = $validatedData[$key];
                }
            }

            $checkIn = CheckIn::create($payload);
            return $checkIn;
        } catch (Exception $e) {
            Log::error("CheckInService::store" . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Display a specific check-in.
     *
     * @param int $id
     * @return mixed
     */
    public function show(int $id)
    {
        try {
            $checkIn = CheckIn::whereHas('goal', function ($query) {
                $query->where('user_id', $this->user->id);
            })
                ->with(['goal'])
                ->find($id);

            if (!$checkIn) {
                throw new Exception('Check-in not found or does not belong to your goals');
            }

            return $checkIn;
        } catch (Exception $e) {
            Log::error("CheckInService::show" . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a specific check-in.
     *
     * @param int $id
     * @param array $validatedData
     * @return mixed
     */
    public function update(int $id, array $validatedData)
    {
        try {
            $checkIn = CheckIn::whereHas('goal', function ($query) {
                $query->where('user_id', $this->user->id);
            })
                ->find($id);

            if (!$checkIn) {
                throw new Exception('Check-in not found or does not belong to your goals');
            }

            $goal = $checkIn->goal;
            if (isset($validatedData['goal_id'])) {
                $goal = Goal::where('id', $validatedData['goal_id'])->where('user_id', $this->user->id)->first();
                if (! $goal) {
                    throw new Exception('Goal not found or does not belong to you');
                }
            }
            $derived = $this->savingsCalculation->getGoalDerived($goal);
            $perDayAllowedSpend = $derived['per_day_allowed_spend'];
            // Recompute calculated_daily_save when daily_spent or amount_spending (legacy) changes.
            $dailySpent = $validatedData['daily_spent_amount'] ?? $validatedData['amount_spending'] ?? null;
            if ($dailySpent !== null && $dailySpent !== '') {
                $validatedData['daily_spent_amount'] = (float) $dailySpent;
                $validatedData['amount_spending'] = (float) $dailySpent;
                // If multiple check-ins on same day: total_daily_spent = sum of all check-ins' daily_spent_amount for that date.
                $checkDate = isset($validatedData['check_date']) ? Carbon::parse($validatedData['check_date'])->startOfDay() : Carbon::parse($checkIn->check_date)->startOfDay();
                $existingDailySpent = (float) CheckIn::where('goal_id', $goal->id)
                    ->whereDate('check_date', $checkDate)
                    ->where('id', '!=', $checkIn->id) // Exclude current check-in being updated.
                    ->sum('daily_spent_amount');
                $totalDailySpent = $existingDailySpent + (float) $dailySpent;

                // আজকে বেঁচেছে = max(0, daily_limit - total_daily_spent)
                $validatedData['calculated_daily_save'] = $this->savingsCalculation->calculateDailySave(
                    $perDayAllowedSpend,
                    $totalDailySpent
                );
                $validatedData['amount_saved'] = $validatedData['calculated_daily_save'];
            } elseif (array_key_exists('amount_saved', $validatedData) && $validatedData['amount_saved'] !== null && $validatedData['amount_saved'] !== '') {
                $validatedData['calculated_daily_save'] = max(0, (float) $validatedData['amount_saved']);
            }
            $checkIn->update($validatedData);

            $checkIn->load(['goal']);

            return $checkIn;
        } catch (Exception $e) {
            Log::error("CheckInService::update" . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a specific check-in.
     *
     * @param int $id
     * @return mixed
     */
    public function destroy(int $id)
    {
        try {
            $checkIn = CheckIn::whereHas('goal', function ($query) {
                $query->where('user_id', $this->user->id);
            })
                ->find($id);

            if (!$checkIn) {
                throw new Exception('Check-in not found or does not belong to your goals');
            }

            $checkIn->delete();

            return $checkIn;
        } catch (Exception $e) {
            Log::error("CheckInService::destroy" . $e->getMessage());
            throw $e;
        }
    }

    public function checkInsActivity($request)
    {
        try {
            $groupBy = $request->group_by ?? 'daily'; // daily, weekly, monthly
            $perPage = $request->per_page ?? 25;

            $checkIns = CheckIn::whereHas('goal', function ($query) {
                $query->where('user_id', $this->user->id);
            })
                ->with(['goal'])
                ->orderBy('check_date', 'desc')
                ->get();

            // Group the data based on the grouping type
            $groupedData = $this->groupCheckIns($checkIns, $groupBy);
            // return $groupedData;
            // Convert to paginated format if needed
            $currentPage = $request->page ?? 1;
            $items = array_slice($groupedData, ($currentPage - 1) * $perPage, $perPage);

            return [
                'status' => true,
                'message' => 'Check-in activity fetched successfully',
                'code' => 200,
                // 'group_by' => $groupBy,
                'data' => $items,
                'pagination' => [
                    'current_page' => $currentPage,
                    'last_page' => ceil(count($groupedData) / $perPage),
                    'per_page' => $perPage,
                    'total' => count($groupedData),
                    'first_page_url' => url()->current() . '?page=1&per_page=' . $perPage . '&group_by=' . $groupBy,
                    'last_page_url' => url()->current() . '?page=' . ceil(count($groupedData) / $perPage) . '&per_page=' . $perPage . '&group_by=' . $groupBy,
                    'next_page_url' => url()->current() . '?page=' . ($currentPage + 1) . '&per_page=' . $perPage . '&group_by=' . $groupBy,
                    'prev_page_url' => url()->current() . '?page=' . ($currentPage - 1) . '&per_page=' . $perPage . '&group_by=' . $groupBy,
                    'from' => ($currentPage - 1) * $perPage + 1,
                    'to' => min($currentPage * $perPage, count($groupedData)),
                    'path' => url()->current(),
                ],
            ];
        } catch (Exception $e) {
            Log::error("CheckInService::index: " . $e->getMessage());
            throw $e;
        }
    }

    private function groupCheckIns($checkIns, $groupBy)
    {
        $grouped = [];

        switch ($groupBy) {
            case 'weekly':
                $grouped = $this->groupByWeek($checkIns);
                break;
            case 'monthly':
                $grouped = $this->groupByMonth($checkIns);
                break;
            case 'daily':
            default:
                $grouped = $this->groupByDay($checkIns);
                break;
        }

        return $grouped;
    }

    private function groupByDay($checkIns)
    {
        $dailyData = [];

        foreach ($checkIns as $checkIn) {
            $date = $checkIn->check_date->format('Y-m-d');
            $dayName = $checkIn->check_date->format('D'); // Sat, Sun, Mon

            if (!isset($dailyData[$date])) {
                $dailyData[$date] = [
                    'date' => $date,
                    'day_name' => $dayName,
                    'amount_saved' => 0,
                    'amount_spending' => 0,
                    'amount_saved_target' => 0,
                    // 'check_ins' => [],
                ];
            }

            $dailyData[$date]['amount_saved'] += (float) $checkIn->amount_saved;
            $dailyData[$date]['amount_spending'] += (float) $checkIn->amount_spending;
            $dailyData[$date]['amount_saved_target'] += (float) $checkIn->target_amount;
            // $dailyData[$date]['check_ins'][] = $checkIn;
        }

        return array_values($dailyData);
    }

    private function groupByWeek($checkIns)
    {
        $weeklyData = [];

        foreach ($checkIns as $checkIn) {
            $year = $checkIn->check_date->format('Y');
            $week = $checkIn->check_date->format('W');
            $key = $year . '-W' . $week;

            if (!isset($weeklyData[$key])) {
                $weeklyData[$key] = [
                    'week' => 'Week ' . (int) $week,
                    'year' => $year,
                    'week_number' => (int) $week,
                    'week_key' => $key,
                    'start_date' => $checkIn->check_date->startOfWeek()->format('Y-m-d'),
                    'end_date' => $checkIn->check_date->endOfWeek()->format('Y-m-d'),
                    'amount_saved' => 0,
                    'amount_spending' => 0,
                    'amount_saved_target' => 0,
                    // 'check_ins' => [],
                ];
            }

            $weeklyData[$key]['amount_saved'] += (float) $checkIn->amount_saved;
            $weeklyData[$key]['amount_spending'] += (float) $checkIn->amount_spending;
            $weeklyData[$key]['amount_saved_target'] += (float) $checkIn->target_amount;
            // $weeklyData[$key]['check_ins'][] = $checkIn;
        }

        return array_values($weeklyData);
    }

    private function groupByMonth($checkIns)
    {
        $monthlyData = [];

        foreach ($checkIns as $checkIn) {
            $monthKey = $checkIn->check_date->format('Y-m');
            // $monthName = $checkIn->check_date->format('F Y'); // January 2025
            $monthName = $checkIn->check_date->format('F'); // January 2025

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [
                    'month' => $monthName,
                    'month_key' => $monthKey,
                    'year' => $checkIn->check_date->format('Y'),
                    'month_number' => $checkIn->check_date->format('m'),
                    'amount_saved' => 0,
                    'amount_spending' => 0,
                    'amount_saved_target' => 0,
                    // 'check_ins' => [],
                ];
            }

            $monthlyData[$monthKey]['amount_saved'] += (float) $checkIn->amount_saved;
            $monthlyData[$monthKey]['amount_spending'] += (float) $checkIn->amount_spending;
            $monthlyData[$monthKey]['amount_saved_target'] += (float) $checkIn->target_amount;
            // $monthlyData[$monthKey]['check_ins'][] = $checkIn;
        }

        return array_values($monthlyData);
    }
}