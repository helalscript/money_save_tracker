<?php

namespace App\Http\Controllers\API\V1\User;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Services\API\V1\User\ProgressPageService;
use Exception;
use Illuminate\Http\Request;
use Log;
use Pest\Console\Help;

class ProgressPageController extends Controller
{
    public function __construct(protected ProgressPageService $progressPageService)
    {
        //
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data = $this->progressPageService->index();

            return Helper::jsonResponse(true, 'Progress page data fetched successfully', 200, $data);
        } catch (Exception $e) {
            Log::error('ProgressPageController::index' . $e->getMessage());
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    public function aiInsights()
    {
        try {
            $data = $this->progressPageService->aiInsights();
            return Helper::jsonResponse(true, 'AI Insights fetched successfully', 200, $data);
        } catch (Exception $e) {
            Log::error('ProgressPageController::aiInsights' . $e->getMessage());
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }


}
