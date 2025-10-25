<?php

namespace App\Services\API\V1\User;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class NotificationService
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
            $unread = $request->unread ?? false;

            if ($unread) {
                $notifications = $this->user->unreadNotifications()
                    ->orderBy('created_at', 'desc')
                    ->paginate($request->$perPage);
            } else {
                $notifications = $this->user->notifications()
                    ->orderBy('created_at', 'desc')
                    ->paginate($request->$perPage);
            }
            return $notifications;

        } catch (Exception $e) {
            Log::error("NotificationService::index" . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Display a specific resource.
     *
     * @param string $id
     * @return mixed
     */
    public function show(string $id)
    {
        try {
            $notification = $this->user->notifications()->where('id', $id)->first();

            if (!$notification) {
                throw new Exception('Notification not found');
            }

            return $notification;

        } catch (Exception $e) {
            Log::error("NotificationService::show" . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a specific resource.
     *
     * @param string $id
     * @return mixed
     */
    public function destroy(string $id)
    {
        try {
            $notification = $this->user->notifications()->where('id', $id)->first();

            if (!$notification) {
                throw new Exception('Notification not found');
            }

            $notification->delete();
        } catch (Exception $e) {
            Log::error("NotificationService::destroy" . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Mark all notifications as read for the user.
     *
     * @return void
     */
    public function markAllAsRead($user)
    {
        try {
            $user->unreadNotifications->markAsRead();
        } catch (Exception $e) {
            Log::error("NotificationService::markAllAsRead" . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete all notifications for the user.
     *
     * @return void
     */
    public function deleteAll()
    {
        try {
            $this->user->notifications()->delete();
            return true;
        } catch (Exception $e) {
            Log::error("NotificationService::deleteAll" . $e->getMessage());
            throw $e;
            ;
        }
    }

}