<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use App\Mail\NotificationMail;
use Illuminate\Support\Facades\Mail;

class NotificationsController extends Controller
{
    /**
     * Display a listing of the notifications.
     */
    public function index()
    {
        $notifications = Notification::all();
        return response()->json($notifications);
    }

    /**
     * Store a newly created notification in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'status' => 'required|in:pending,sent,failed,scheduled',
            'send_via_smtp' => 'sometimes|boolean',
            'company_id' => 'required|exists:companies,id'
        ]);

        $notification = new Notification($request->all());

        $notification->save();

        if ($request->send_via_smtp) {
            $this->sendEmailNotification($notification);
        }

        return response()->json($notification, 201);
    }

    /**
     * Display the specified notification.
     */
    public function show(Notification $notification)
    {
        return response()->json($notification);
    }

    /**
     * Update the specified notification in storage.
     */
    public function update(Request $request, Notification $notification)
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'message' => 'sometimes|string',
            'status' => 'sometimes|in:pending,sent,failed,scheduled',
            'read' => 'sometimes|boolean',
            'archived' => 'sometimes|boolean',
            'send_via_smtp' => 'sometimes|boolean'
        ]);

        $notification->update($request->all());

        if ($request->send_via_smtp && $notification->status === 'pending') {
            $this->sendEmailNotification($notification);
            $notification->update(['status' => 'sent']);
        }

        return response()->json($notification);
    }

    /**
     * Remove the specified notification from storage.
     */
    public function destroy(Notification $notification)
    {
        $notification->delete();
        return response()->json(['message' => 'Notification deleted successfully']);
    }

    /**
     * Send an email notification.
     */
    protected function sendEmailNotification(Notification $notification)
    {
        // Should define more mail sending logic here, exmple:
        Mail::to('contact@medaminebt.com')->send(new NotificationMail($notification));
    }
}
