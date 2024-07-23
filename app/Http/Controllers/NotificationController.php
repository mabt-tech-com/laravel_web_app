<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function index()
    {
        try {
            return Notification::all();
        } catch (\Exception $e) {
            Log::error('Error fetching notifications: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch notifications'], 500);
        }
    }

    public function show($id)
    {
        try {
            return Notification::findOrFail($id);
        } catch (\Exception $e) {
            Log::error('Error fetching notification: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch notification'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $notification = Notification::create($request->all());

            if ($notification->scheduled_at) {
                $notification->status = 'scheduled';
            } else {
                $this->sendNotification($notification);
            }


        // Schedule the notification if scheduled_at is set
        if ($notification->scheduled_at) {
            // Schedule logic here, possibly with a job/queue
        } else {
            // Send the notification immediately
            $this->sendNotification($notification);

        }
    }

    public function update(Request $request, $id)
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification->update($request->all());

            if ($notification->scheduled_at && $notification->status == 'pending') {
                $notification->status = 'scheduled';
            }

            $notification->save();
            return response()->json($notification, 200);
        } catch (\Exception $e) {
            Log::error('Error updating notification: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update notification'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('Error deleting notification: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete notification'], 500);
        }
    }

    public function markAsRead($id)
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification->read = true;
            $notification->save();
            return response()->json($notification, 200);
        } catch (\Exception $e) {
            Log::error('Error marking notification as read: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to mark notification as read'], 500);
        }
    }

    public function archive($id)
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification->archived = true;
            $notification->save();
            return response()->json($notification, 200);
        } catch (\Exception $e) {
            Log::error('Error archiving notification: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to archive notification'], 500);
        }
    }

    private function sendNotification(Notification $notification)
    {
        try {
            // Logic to send in-app notification

            if ($notification->send_via_smtp) {
                Mail::raw($notification->message, function ($message) use ($notification) {
                    $message->to('contact@medaminebt.com')
                        ->subject($notification->title);
                });
            }

            $notification->status = 'sent';
            $notification->sent_at = Carbon::now();
        } catch (\Exception $e) {
            Log::error('Error sending notification: ' . $e->getMessage());
            $notification->status = 'failed';
        }
    }
}
