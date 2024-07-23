<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10); // Default to 10 items per page
            $notifications = Notification::paginate($perPage);
            return response()->json($notifications);
        } catch (\Exception $e) {
            Log::error('Error in NotificationController@index: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch notifications'], 500);
        }
    }

    public function show($id)
    {
        try {
            $notification = Notification::findOrFail($id);
            return response()->json($notification);
        } catch (\Exception $e) {
            Log::error('Error in NotificationController@show: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch notification'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'user_id' => 'nullable|exists:users,id',
                'role_id' => 'nullable|exists:roles,id',
                'scheduled_at' => 'nullable|date',
                'send_via_smtp' => 'boolean',
            ]);

            $notification = Notification::create($request->all());

            // Schedule the notification if scheduled_at is set
            if ($notification->scheduled_at) {
                // Schedule logic here, possibly with a job/queue
            } else {
                // Send the notification immediately
                $this->sendNotification($notification);
            }

            return response()->json($notification, 201);
        } catch (\Exception $e) {
            Log::error('Error in NotificationController@store: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create notification'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $notification = Notification::findOrFail($id);

            $request->validate([
                'title' => 'string|max:255',
                'message' => 'string',
                'user_id' => 'nullable|exists:users,id',
                'role_id' => 'nullable|exists:roles,id',
                'scheduled_at' => 'nullable|date',
                'send_via_smtp' => 'boolean',
            ]);

            $notification->update($request->all());

            return response()->json($notification);
        } catch (\Exception $e) {
            Log::error('Error in NotificationController@update: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update notification'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification->delete();

            return response()->json(['message' => 'Notification deleted']);
        } catch (\Exception $e) {
            Log::error('Error in NotificationController@destroy: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete notification'], 500);
        }
    }

    public function markAsRead($id)
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification->update(['read' => true]);

            return response()->json(['message' => 'Notification marked as read']);
        } catch (\Exception $e) {
            Log::error('Error in NotificationController@markAsRead: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to mark notification as read'], 500);
        }
    }

    public function markAsUnread($id)
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification->update(['read' => false]);

            return response()->json(['message' => 'Notification marked as unread']);
        } catch (\Exception $e) {
            Log::error('Error in NotificationController@markAsUnread: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to mark notification as unread'], 500);
        }
    }

    public function archive($id)
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification->update(['archived' => true]);

            return response()->json(['message' => 'Notification archived']);
        } catch (\Exception $e) {
            Log::error('Error in NotificationController@archive: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to archive notification'], 500);
        }
    }

    public function unarchive($id)
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification->update(['archived' => false]);

            return response()->json(['message' => 'Notification unarchived']);
        } catch (\Exception $e) {
            Log::error('Error in NotificationController@unarchive: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to unarchive notification'], 500);
        }
    }

    private function sendNotification(Notification $notification)
    {
        try {
            // Logic to send in-app notification
            if ($notification->send_via_smtp) {
                $user = $notification->user;
                if ($user) {
                    Mail::to($user->email)->send(new \App\Mail\NotificationMail($notification));
                }
            }

            $notification->update(['status' => 'sent', 'sent_at' => now()]);
        } catch (\Exception $e) {
            Log::error('Error in NotificationController@sendNotification: ' . $e->getMessage());
        }
    }
}
