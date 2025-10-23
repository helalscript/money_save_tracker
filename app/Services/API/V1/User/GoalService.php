<?php

namespace App\Services\API\V1\User;

use App\Models\Goal;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

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

            $goals = Goal::where('user_id', $this->user->id)
                ->with(['checkIns' => function($query) {
                    $query->orderBy('check_date', 'desc');
                }])
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
            $validatedData['user_id'] = $this->user->id;
            
            $goal = Goal::create($validatedData);
            
            $goal->load(['checkIns' => function($query) {
                $query->orderBy('check_date', 'desc');
            }]);

            return $goal;
        } catch (Exception $e) {
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
            $goal = Goal::where('id', $id)
                ->where('user_id', $this->user->id)
                ->with(['checkIns' => function($query) {
                    $query->orderBy('check_date', 'desc');
                }])
                ->first();

            if (!$goal) {
                throw new Exception('Goal not found or does not belong to you');
            }

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
            
            $goal->load(['checkIns' => function($query) {
                $query->orderBy('check_date', 'desc');
            }]);

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
}