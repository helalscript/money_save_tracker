<?php

namespace App\Services\V1\Admin;

use App\Models\TimePackage;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TimePackageService
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
            $timePackages = TimePackage::select('id', 'name', 'description', 'day', 'is_unlimited')
                ->where('status', 'active')
                ->paginate($perPage);

            return $timePackages;
        } catch (Exception $e) {
            Log::error('TimePackageService::index'.$e->getMessage());
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
            $timePackage = TimePackage::create($validatedData);
            return $timePackage;
        } catch (Exception $e) {
            Log::error('TimePackageService::store'.$e->getMessage());
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
            $timePackage = TimePackage::find($id);
            if (!$timePackage) {
                throw new Exception('Time package not found');
            }
            return $timePackage;

        } catch (Exception $e) {
            Log::error('TimePackageService::show'.$e->getMessage());
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
            $timePackage = TimePackage::find($id);
            if (!$timePackage) {
                throw new Exception('Time package not found');
            }
            $timePackage->update($validatedData);
            return $timePackage;
        } catch (Exception $e) {
            Log::error('TimePackageService::update'.$e->getMessage());
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
            $timePackage = TimePackage::find($id);
            if (!$timePackage) {
                throw new Exception('Time package not found');
            }
            $timePackage->delete();
            return $timePackage;
        } catch (Exception $e) {
            Log::error('TimePackageService::destroy'.$e->getMessage());
            throw $e;
        }
    }
}
