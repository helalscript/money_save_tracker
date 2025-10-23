<?php

namespace App\Services\API\V1\Public;

use App\Models\Package;
use Exception;
use Illuminate\Support\Facades\Log;

class PackageService
{
    /**
     * Fetch all active packages with their features.
     *
     * @return mixed
     */
    public function index($request)
    {
        try {
            $perPage = $request->per_page ?? 25;

            $packages = Package::where('status', 'active')
                ->select('id', 'title', 'price_monthly', 'description', 'image','free_trail_day')
                ->with(['features' => function($query) {
                    // $query->select('id', 'package_id', 'feature_name','description');
                }])
                ->orderBy('price_monthly', 'asc')
                ->paginate($perPage);

            return $packages;
        } catch (Exception $e) {
            Log::error("PackageService::index" . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Display a specific package with its features.
     *
     * @param int $id
     * @return mixed
     */
    public function show(int $id)
    {
        try {
            $package = Package::where('id', $id)
                ->where('status', 'active')
                ->with(['features' => function($query) {
                    $query->where('status', 'active');
                }])
                ->first();

            if (!$package) {
                throw new Exception('Package not found or inactive');
            }

            return $package;
        } catch (Exception $e) {
            Log::error("PackageService::show" . $e->getMessage());
            throw $e;
        }
    }
}