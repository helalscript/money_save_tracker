<?php

namespace App\Http\Controllers\API\V1\Admin\System;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Services\API\V1\Admin\System\DynamicPageService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DynamicPageController extends Controller
{
    protected $dynamicPageService;

    public function __construct(DynamicPageService $dynamicPageService)
    {
        $this->dynamicPageService = $dynamicPageService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $dynamicPages = $this->dynamicPageService->index($request);

            return Helper::jsonResponse(true, 'Dynamic pages fetched successfully', 200, $dynamicPages, true);
        } catch (Exception $e) {
            Log::error('DynamicPageController::index'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validateData = $request->validate([
            'page_title' => 'required|string|max:100',
            'page_content' => 'required|string',
        ]);

        try {
            $dynamicPage = $this->dynamicPageService->store($validateData);

            return Helper::jsonResponse(true, 'Dynamic page created successfully', 200, $dynamicPage);
        } catch (Exception $e) {
            Log::error('DynamicPageController::store'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $dynamicPage = $this->dynamicPageService->show($id);

            return Helper::jsonResponse(true, 'Dynamic page fetched successfully', 200, $dynamicPage);
        } catch (Exception $e) {
            Log::error('DynamicPageController::show'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validateData = $request->validate([
            'page_title' => 'required|string|max:100',
            'page_content' => 'required|string',
        ]);

        try {
            $dynamicPage = $this->dynamicPageService->update($id, $validateData);

            return Helper::jsonResponse(true, 'Dynamic page updated successfully', 200, $dynamicPage);
        } catch (Exception $e) {
            Log::error('DynamicPageController::update'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $dynamicPage = $this->dynamicPageService->destroy($id);

            return Helper::jsonResponse(true, 'Dynamic page deleted successfully', 200);
        } catch (Exception $e) {
            Log::error('DynamicPageController::destroy'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }
}
