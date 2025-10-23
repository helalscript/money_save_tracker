<?php

namespace App\Http\Controllers\API\V1\Public;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Services\API\V1\Public\StepperPageService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StepperPageController extends Controller
{
    protected $service;

    public function __construct(StepperPageService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $pages = $this->service->index($request);

            return Helper::jsonResponse(true, 'Stepper pages fetched successfully', 200, $pages, true);
        } catch (Exception $e) {
            Log::error('StepperPageController::index'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $page = $this->service->show((int) $id);

            return Helper::jsonResponse(true, 'Stepper page fetched successfully', 200, $page);
        } catch (Exception $e) {
            Log::error('StepperPageController::show'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }
}
