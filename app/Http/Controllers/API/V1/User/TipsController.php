<?php

namespace App\Http\Controllers\API\V1\User;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\UserTip;
use App\Services\API\V1\User\TipsService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class TipsController extends Controller
{
    protected $tipsService;

    public function __construct(TipsService $tipsService)
    {
        $this->tipsService = $tipsService;
    }

    /**
     * Get today's tips (3 per day)
     * GET /api/tips/today
     */
    public function getTodayTips(Request $request)
    {
        try {
            $tips = $this->tipsService->getDailyTips();
            return Helper::jsonResponse(true, 'Tips fetched successfully', 200, $tips);
        } catch (Exception $e) {
            Log::error('TipsController::getTodayTips' . $e->getMessage());
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Mark tip as done
     * POST /api/tips/{id}/mark-done
     */
    public function markAsDone($id)
    {
        try {
            $userTip = $this->tipsService->markAsDone($id);

            return response()->json([
                'success' => true,
                'message' => 'Tip marked as done!',
                'data' => $userTip,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tip not found or already completed.',
            ], 404);
        }
    }

    /**
     * Get tip history
     * GET /api/tips/history
     */
    public function getHistory(Request $request)
    {
        $userId = auth()->id();
        $type = $request->query('type');

        $history = $this->tipsService->getTipHistory($userId, $type);

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * Refresh today's tips (get new random tips)
     * POST /api/tips/refresh
     */
    public function refreshTips(Request $request)
    {
        try {
            $tips = $this->tipsService->refreshTips();
            return Helper::jsonResponse(true, 'Tips refreshed successfully', 200, $tips);
        } catch (Exception $e) {
            Log::error('TipsController::refreshTips' . $e->getMessage());
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }
}
