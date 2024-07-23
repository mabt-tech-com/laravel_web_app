<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        try {
            Log::info('Memory usage before query: ' . memory_get_usage());
            $notifications = Notification::paginate(10); // Adding pagination
            Log::info('Memory usage after query: ' . memory_get_usage());
            return response()->json($notifications);
        } catch (\Exception $e) {
            Log::error('Error in NotificationController@index: ' . $e->getMessage());
            Log::error('Error Trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Failed to fetch notifications'], 500);
        }
    }

    public function show($id)
    {
        try {
            Log::info('Memory usage before find: ' . memory_get_usage());
            $notification = Notification::findOrFail($id);
            Log::info('Memory usage after find: ' . memory_get_usage());
            return response()->json($notification);
        } catch (\Exception $e) {
            Log::error('Error in NotificationController@show: ' . $e->getMessage());
            Log::error('Error Trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Failed to fetch notification'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'scheduled_at' => 'nullable|date',
                'send_via_smtp' => 'boolean',
            ]);

            $notification = Notification::create($request->all());

            if ($notification->scheduled_at) {
                Log::info('Scheduled notification for: ' . $notification->scheduled_at);
                $this->sendNotification($notification); // Placeholder for actual scheduling logic
            } else {
                $this->sendNotification($notification);
            }

            return response()->json($notification, 201);
        } catch (\Exception $e) {
            Log::error('Error in NotificationController@store: ' . $e->getMessage());
            Log::error('Error Trace: ' . $e->getTraceAsString());
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
                'scheduled_at' => 'nullable|date',
                'send_via_smtp' => 'boolean',
            ]);

            $notification->update($request->all());

            return response()->json($notification);
        } catch (\Exception $e) {
            Log::error('Error in NotificationController@update: ' . $e->getMessage());
            Log::error('Error Trace: ' . $e->getTraceAsString());
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
            Log::error('Error Trace: ' . $e->getTraceAsString());
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
            Log::error('Error Trace: ' . $e->getTraceAsString());
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
            Log::error('Error Trace: ' . $e->getTraceAsString());
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
            Log::error('Error Trace: ' . $e->getTraceAsString());
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
            Log::error('Error Trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Failed to unarchive notification'], 500);
        }
    }

    private function sendNotification(Notification $notification)
    {
        try {
            // Logic to send in-app notification
            if ($notification->send_via_smtp) {
                // Send the notification to all users
                $users = User::all();
                foreach ($users as $user) {
                    $message = "This is a notification: " . $notification->title;
                    Mail::raw($message, function ($mail) use ($user, $notification) {
                        $mail->to($user->email)
                            ->subject($notification->title);
                    });
                }
            }

            $notification->update(['status' => 'sent', 'sent_at' => now()]);
        } catch (\Exception $e) {
            Log::error('Error in NotificationController@sendNotification: ' . $e->getMessage());
            Log::error('Error Trace: ' . $e->getTraceAsString());
        }
    }
}
