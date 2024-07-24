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
            $this->createInAppNotification($notification->user_id, $notification);
        } else if ($notification->role_id) {
            // Send notification to users with specific role
            $users = User::where('role_id', $notification->role_id)->get();
            foreach ($users as $user) {
                $this->createInAppNotification($user->id, $notification);
            }
        } else {
            // Send notification to all users
            $users = User::all();
            foreach ($users as $user) {
                $this->createInAppNotification($user->id, $notification);
            }
        }

        // Send SMTP notification if enabled
        if ($notification->send_via_smtp) {
            $this->sendEmailNotification($notification);
        }
    }

    protected function createInAppNotification($userId, $notification)
    {
        Notification::create([
            'company_id' => $notification->company_id,
            'title' => $notification->title,
            'message' => $notification->message,
            'user_id' => $userId,
            'role_id' => $notification->role_id,
            'scheduled_at' => $notification->scheduled_at,
            'sent_at' => $notification->sent_at,
            'status' => 'sent',
            'send_via_smtp' => $notification->send_via_smtp,
            'read' => false,
            'archived' => false,
        ]);
    }

    protected function sendEmailNotification(Notification $notification)
    {
        $users = collect();
        if ($notification->user_id) {
            $users->push(User::find($notification->user_id));
        } elseif ($notification->role_id) {
            $users = User::where('role_id', $notification->role_id)->get();
        } else {
            $users = User::all();
        }

        foreach ($users as $user) {
            Mail::to($user->email)->send(new \App\Mail\NotificationMail($notification));
        }
    }
}
