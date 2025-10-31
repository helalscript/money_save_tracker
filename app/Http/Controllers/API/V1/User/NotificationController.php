<?php

namespace App\Http\Controllers\API\V1\User;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Services\API\V1\User\NotificationService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function __construct(protected NotificationService $notificationService)
    {
        //
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $notifications = $this->notificationService->index($request);
            return Helper::jsonResponse(true, 'Notifications fetched successfully', 200, $notifications, true);
        } catch (Exception $e) {
            Log::error('NotificationController::index' . $e->getMessage());
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $notification = $this->notificationService->show($id);
            return Helper::jsonResponse(true, 'Notification fetched successfully', 200, $notification);
        } catch (Exception $e) {
            Log::error('NotificationController::show' . $e->getMessage());
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }


    public function destroy(string $id)
    {
        try {
            $this->notificationService->destroy($id);
            return Helper::jsonResponse(true, 'Notification deleted successfully', 200);
        } catch (Exception $e) {
            Log::error('NotificationController::delete' . $e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    public function markAllAsRead(Request $request)
    {
        try {
            $this->notificationService->markAllAsRead($request->user());

            return Helper::jsonResponse(true, 'All notifications marked as read', 200);
        } catch (Exception $e) {
            Log::error('NotificationController::markAllAsRead' . $e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    public function deleteAll()
    {
        try {
            $this->notificationService->deleteAll();
            return Helper::jsonResponse(true, 'All notifications deleted successfully.', 200);
        } catch (Exception $e) {
            Log::error('NotificationController::deleteAll Error: ' . $e->getMessage());
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

}
