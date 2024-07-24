<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Jobs\SendNotificationJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function sendNotification(array $data)
    {
        // Create notification
        $notification = Notification::create($data);

        // Schedule the notification if scheduled_at is set
        if (isset($data['scheduled_at'])) {
            SendNotificationJob::dispatch($notification)->delay(new Carbon($data['scheduled_at']));
        } else {
            $this->deliverNotification($notification);
        }

        return $notification;
    }

    public function deliverNotification(Notification $notification)
    {
        // Set notification as sent
        $notification->update(['status' => 'sent', 'sent_at' => Carbon::now()]);

        // Send in-app notification
        if ($notification->user_id) {
            // Send notification to specific user
            $user = User::find($notification->user_id);
            // Implement the logic to send in-app notification
        } else if ($notification->role_id) {
            // Send notification to users with specific role
            $users = User::where('role_id', $notification->role_id)->get();
            foreach ($users as $user) {
                // Implement the logic to send in-app notification
            }
        } else {
            // Send notification to all users
            $users = User::all();
            foreach ($users as $user) {
                // Implement the logic to send in-app notification
            }
        }

        // Send SMTP notification if enabled
        if ($notification->send_via_smtp) {
            Mail::to($user->email)->send(new \App\Mail\NotificationMail($notification));
        }
    }
}
