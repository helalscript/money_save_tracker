<?php

namespace App\Http\Controllers\API\V1\Public;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Services\API\V1\Public\GeneralSettingService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GeneralSettingController extends Controller
{
    protected $generalSettingService;

    public function __construct(GeneralSettingService $generalSettingService)
    {
        $this->generalSettingService = $generalSettingService;
    }

    public function getSystemInfo()
    {
        try {
            $systemInfo = $this->generalSettingService->getSystemInfo();

            return Helper::jsonResponse(true, 'System info fetched successfully', 200, $systemInfo);
        } catch (Exception $e) {
            Log::error('GeneralSettingController::getSystemInfo'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    public function getDynamicPages(Request $request)
    {
        try {
            $dynamicPages = $this->generalSettingService->getDynamicPages($request);

            return Helper::jsonResponse(true, 'Dynamic pages fetched successfully', 200, $dynamicPages, true);
        } catch (Exception $e) {
            Log::error('GeneralSettingController::getDynamicPages'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    public function showDaynamicPage($page_slug)
    {
        try {
            $dynamicPage = $this->generalSettingService->showDaynamicPage($page_slug);

            return Helper::jsonResponse(true, 'Dynamic page fetched successfully', 200, $dynamicPage);
        } catch (Exception $e) {
            Log::error('GeneralSettingController::showDaynamicPage'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    public function getFaqs(Request $request)
    {
        try {
            $faqs = $this->generalSettingService->getFaqs($request);

            return Helper::jsonResponse(true, 'FAQs fetched successfully', 200, $faqs, true);
        } catch (Exception $e) {
            Log::error('GeneralSettingController::getFaqs'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }
}
