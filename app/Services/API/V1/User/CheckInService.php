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

            $checkIns = CheckIn::whereHas('goal', function($query) {
                    $query->where('user_id', $this->user->id);
                })
                ->with(['goal:id,title,goal_type,target_amount,status'])
                ->orderBy('check_date', 'desc')
                ->paginate($perPage);

            return $checkIns;
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
    public function 
    <div class=""></div>store(array $validatedData)
    {
        try {
            // Verify the goal belongs to the authenticated user
            $goal = Goal::where('id', $validatedData['goal_id'])
                ->where('user_id', $this->user->id)
                ->first();

            if (!$goal) {
                throw new Exception('Goal not found or does not belong to you');
            }
            
            $checkIn = CheckIn::create($validatedData);
            
            $checkIn->load(['goal:id,title,goal_type,target_amount,status']);

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
            $checkIn = CheckIn::whereHas('goal', function($query) {
                    $query->where('user_id', $this->user->id);
                })
                ->with(['goal:id,title,goal_type,target_amount,status'])
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
            $checkIn = CheckIn::whereHas('goal', function($query) {
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
            
            $checkIn->load(['goal:id,title,goal_type,target_amount,status']);

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
            $checkIn = CheckIn::whereHas('goal', function($query) {
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
}