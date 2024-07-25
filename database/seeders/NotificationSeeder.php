<?php


use Illuminate\Database\Seeder;
use Illuminate\Notifications\Notification;


class NotificationSeeder extends Seeder
{
    public function run()
    {
        $statuses = ['pending', 'sent', 'failed', 'scheduled'];

        foreach ($statuses as $status) {
            Notification::create([
                'title' => "Notification - $status",
                'message' => "This is a $status notification.",
                'status' => $status,
                'read' => false,
                'archived' => false,
                'company_id' => 1,
                'send_via_smtp' => false,
            ]);
        }
    }
}
