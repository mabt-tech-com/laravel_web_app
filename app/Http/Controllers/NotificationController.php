<?php


namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        Log::info('NotificationController@index: Request received');

        try {
            $notifications = Notification::all();
            if ($notifications->isEmpty()) {
                Log::info('NotificationController@index: No notifications found');
                return response()->json(['message' => 'No notifications found'], 404);
            }
            return response()->json($notifications);
        } catch (\Exception $e) {
            Log::error('NotificationController@index: Error fetching notifications: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching notifications'], 500);
        }
    }


    public function show($id)
    {
        try {
            $notification = Notification::findOrFail($id);
            return response()->json($notification);
        } catch (\Exception $e) {
            Log::error('Notification not found: ' . $e->getMessage());
            return response()->json(['message' => 'Notification not found'], 404);
        }
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

        try {
            $notification = $this->notificationService->sendNotification($data);
            return response()->json(['message' => 'Notification created successfully.', 'notification' => $notification]);
        } catch (\Exception $e) {
            Log::error('Error creating notification: ' . $e->getMessage());
            return response()->json(['message' => 'Error creating notification'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $notification = Notification::findOrFail($id);

            $data = $request->validate([
                'title' => 'string|max:255',
                'message' => 'string',
                'read' => 'boolean',
                'archived' => 'boolean',
            ]);

            $notification->update($data);

            return response()->json(['message' => 'Notification updated successfully.', 'notification' => $notification]);
        } catch (\Exception $e) {
            Log::error('Error updating notification: ' . $e->getMessage());
            return response()->json(['message' => 'Error updating notification'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification->delete();

            return response()->json(['message' => 'Notification deleted successfully.']);
        } catch (\Exception $e) {
            Log::error('Error deleting notification: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting notification'], 500);
        }
    }
}
