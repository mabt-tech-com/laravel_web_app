<?php

namespace App\Services;

use App\Jobs\SendNotificationJob;
use App\Mail\NotificationMail;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function scheduleNotification($data)
    {
        $notification = Notification::create($data);
        // Assuming 'scheduled_at' is provided in $data and uses Carbon instance
        if (isset($data['scheduled_at']) && $data['scheduled_at'] > now()) {
            // Schedule job to send notification later
            SendNotificationJob::dispatch($notification)->delay($data['scheduled_at']);
        } else {
            // Send immediately
            $this->sendNotification($notification);
        }
    }

    public function sendNotification(Notification $notification)
    {
        if ($notification->send_via_smtp) {
            $this->sendEmailNotification($notification);
        }
        // Update notification status as sent
        $notification->update(['status' => 'sent', 'sent_at' => now()]);
    }

    protected function sendEmailNotification(Notification $notification)
    {
        // Send email logic
        Mail::to($notification->user->email)->send(new NotificationMail($notification));
    }

    public function fetchNotificationsByUserId($userId)
    {
        return Notification::where('user_id', $userId)->where('archived', false)->get();
    }

    public function fetchNotificationsByRoleId($roleId)
    {
        return Notification::where('role_id', $roleId)->where('archived', false)->get();
    }
}
