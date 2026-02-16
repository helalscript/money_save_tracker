<?php

namespace App\Services\API\V1\User;

use App\Models\CheckIn;
use App\Models\Goal;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CheckInService
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * Fetch all check-ins for the authenticated user's goals.
     *
     * @return mixed
     */
    public function index($request)
    {
        try {
            $perPage = $request->per_page ?? 25;

            $checkIns = CheckIn::whereHas('goal', function ($query) {
                $query->where('user_id', $this->user->id);
            })
                // ->select('id', 'amount_spending','check_date')
                // ->with(['goal'])
                ->orderBy('check_date', 'desc')
                ->where('check_date', '>=', now()->startOfDay())
                // ->selectRaw('SUM(amount_spending) as total_amount_spending')
                ->paginate($perPage);
            $totalAmountSpending = $checkIns->getCollection()->sum('amount_spending');
            $totalAmountSaved = max(0,$totalAmountSpending - ($checkIns->getCollection()->first()->target_amount ?? 0)); //if minus then 0 else total amount saved
            $data = [
                'total_amount_spending' => $totalAmountSpending,
                'total_amount_saved' => $totalAmountSaved,
                'check_ins' => $checkIns->items(),
                'pagination' => [
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
                ],
            ];
            return $data;
            // return $checkIns;
        } catch (Exception $e) {
            Log::error("CheckInService::index" . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Store a new check-in.
     *
     * @param array $validatedData
     * @return mixed
     */
    public function store(array $validatedData)
    {
        try {
            // Verify the goal belongs to the authenticated user
            $goal = Goal::where('user_id', $this->user->id)
                ->where('status', 'active')
                ->first();

            if (!$goal) {
                throw new Exception('Goal not found or does not belong to you');
            }
            $validatedData['goal_id'] = $goal->id;
            $validatedData['target_amount'] = $goal->daily_target ?? 0;
            $validatedData['check_date'] = now();
            $validatedData['status'] = 'completed';
            // dd( $validatedData);
            $checkIn = CheckIn::create($validatedData);

            // $checkIn->load(['goal:id,title,goal_type,target_amount,status']);

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

            // Verify the goal belongs to the authenticated user if goal_id is being updated
            if (isset($validatedData['goal_id'])) {
                $goal = Goal::where('id', $validatedData['goal_id'])
                    ->where('user_id', $this->user->id)
                    ->first();

                if (!$goal) {
                    throw new Exception('Goal not found or does not belong to you');
                }
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