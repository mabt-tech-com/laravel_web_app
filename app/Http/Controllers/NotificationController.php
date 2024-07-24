<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $notifications = Notification::all();
        return response()->json($notifications);
    }

    public function show($id)
    {
        $notification = Notification::findOrFail($id);
        return response()->json($notification);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_id' => 'required|integer|exists:companies,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'user_id' => 'nullable|integer|exists:users,id',
            'role_id' => 'nullable|integer|exists:roles,id',
            'scheduled_at' => 'nullable|date',
            'send_via_smtp' => 'boolean',
        ]);

        $notification = $this->notificationService->sendNotification($data);

        return response()->json(['message' => 'Notification created successfully.', 'notification' => $notification]);
    }

    public function update(Request $request, $id)
    {
        $notification = Notification::findOrFail($id);

        $data = $request->validate([
            'title' => 'string|max:255',
            'message' => 'string',
            'read' => 'boolean',
            'archived' => 'boolean',
        ]);

        $notification->update($data);

        return response()->json(['message' => 'Notification updated successfully.', 'notification' => $notification]);
    }

    public function destroy($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->delete();

        return response()->json(['message' => 'Notification deleted successfully.']);
    }
}
