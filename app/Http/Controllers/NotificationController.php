<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        $notifications = Notification::all();
        return response()->json($notifications);
    }

    public function show($id)
    {
        $notification = Notification::find($id);
        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }
        return response()->json($notification);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'user_id' => 'nullable|exists:users,id',
            'role_id' => 'nullable|exists:roles,id',
            'scheduled_at' => 'nullable|date',
            'send_via_smtp' => 'boolean',
        ]);

        $notification = Notification::create($request->all());

        if ($notification->scheduled_at) {
            // Schedule logic here, possibly with a job/queue
        } else {
            $this->sendNotification($notification);
        }

        return response()->json($notification, 201);
    }

    public function update(Request $request, $id)
    {
        $notification = Notification::find($id);
        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

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
    }

    public function destroy($id)
    {
        $notification = Notification::find($id);
        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->delete();

        return response()->json(['message' => 'Notification deleted']);
    }

    public function markAsRead($id)
    {
        $notification = Notification::find($id);
        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->update(['read' => true]);

        return response()->json(['message' => 'Notification marked as read']);
    }

    public function markAsUnread($id)
    {
        $notification = Notification::find($id);
        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->update(['read' => false]);

        return response()->json(['message' => 'Notification marked as unread']);
    }

    public function archive($id)
    {
        $notification = Notification::find($id);
        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->update(['archived' => true]);

        return response()->json(['message' => 'Notification archived']);
    }

    public function unarchive($id)
    {
        $notification = Notification::find($id);
        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->update(['archived' => false]);

        return response()->json(['message' => 'Notification unarchived']);
    }

    private function sendNotification(Notification $notification)
    {
        // Logic to send in-app notification
        if ($notification->send_via_smtp) {
            $user = $notification->user;
            if ($user) {
                Mail::to($user->email)->send(new \App\Mail\NotificationMail($notification));
            }
        }

        $notification->update(['status' => 'sent', 'sent_at' => now()]);
    }
}
