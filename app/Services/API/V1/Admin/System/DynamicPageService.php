<?php

namespace App\Services\API\V1\Admin\System;

use App\Models\DynamicPage;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DynamicPageService
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
    public function index($request)
    {
        try {

            $perPage = $request->per_page ?? 25;
            $dynamicPages = DynamicPage::select('id', 'page_title', 'page_slug','status')
                ->paginate($perPage);

            return $dynamicPages;
        } catch (Exception $e) {
            Log::error('DynamicPageService::index'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Store a new resource.
     *
     * @return mixed
     */
    public function store(array $validatedData)
    {
        try {
            $validatedData['page_slug'] = generateUniqueSlug($validatedData['page_title']);
            $dynamicPage = DynamicPage::create($validatedData);

            return $dynamicPage;

        } catch (Exception $e) {
            Log::error('DynamicPageService::store'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Display a specific resource.
     *
     * @return mixed
     */
    public function show(int $id)
    {
        try {
            $dynamicPage = DynamicPage::find($id);
            if (! $dynamicPage) {
                throw new Exception('Dynamic page not found');
            }

            return $dynamicPage;

        } catch (Exception $e) {
            Log::error('DynamicPageService::show'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a specific resource.
     *
     * @return mixed
     */
    public function update(int $id, array $validatedData)
    {
        try {
            $dynamicPage = DynamicPage::find($id);
            if (! $dynamicPage) {
                throw new Exception('Dynamic page not found');
            }
            $validatedData['page_slug'] = generateUniqueSlug($validatedData['page_title'], $id);
            $dynamicPage->update($validatedData);

            return $dynamicPage;

        } catch (Exception $e) {
            Log::error('DynamicPageService::update'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a specific resource.
     *
     * @return mixed
     */
    public function destroy(int $id)
    {
        try {
            $dynamicPage = DynamicPage::find($id);
            if (! $dynamicPage) {
                throw new Exception('Dynamic page not found');
            }
            $dynamicPage->delete();

            return $dynamicPage;
        } catch (Exception $e) {
            Log::error('DynamicPageService::destroy'.$e->getMessage());
            throw $e;
        }
    }
}
