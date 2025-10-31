<?php

namespace App\Services\API\V1\Public;

use App\Models\DynamicPage;
use App\Models\Faq;
use App\Models\SystemSetting;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GeneralSettingService
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * Fetch all resources.
     *
     * @return mixed
     */
    public function getSystemInfo()
    {
        try {

            $systemInfo = SystemSetting::select('id', 'system_name', 'logo', 'favicon','footer_logo', 'copyright_text', 'address', 'company_open_hour', 'description', 'contact_number', 'address', 'email')->first();

            return $systemInfo;
        } catch (Exception $e) {
            Log::error('GeneralSettingService::index'.$e->getMessage());
            throw $e;
        }
    }

    public function getDynamicPages($request)
    {
        try {
            $perPage = $request->per_page ?? 25;
            $dynamicPages = DynamicPage::select('id', 'page_title', 'page_slug')
                ->paginate($perPage);

            return $dynamicPages;

        } catch (Exception $e) {
            Log::error('GeneralSettingService::getDynamicPages'.$e->getMessage());
            throw $e;
        }
    }

    public function showDaynamicPage($page_slug)
    {
        try {
            $dynamicPage = DynamicPage::where('page_slug', $page_slug)
                ->where('status', 'active')
                ->select('id', 'page_title', 'page_slug', 'page_content', 'status')
                ->first();

            return $dynamicPage;
        } catch (Exception $e) {
            Log::error('GeneralSettingService::showDaynamicPage'.$e->getMessage());
            throw $e;
        }
    }

    public function getFaqs($request)
    {
        try {
            $perPage = $request->per_page ?? 25;
            $faqs = Faq::select('id', 'question', 'answer')
                ->paginate($perPage);
            return $faqs;

        } catch (Exception $e) {
            Log::error('GeneralSettingService::getFaqs'.$e->getMessage());
            throw $e;
        }
    }
}
