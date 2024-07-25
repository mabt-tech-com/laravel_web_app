<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Notification::create([
            'company_id' => 1,
            'title' => 'Welcome Notification',
            'message' => 'Welcome to our platform!',
            'user_id' => null,
            'role_id' => null,
            'scheduled_at' => null,
            'sent_at' => Carbon::now(),
            'status' => 'sent',
            'send_via_smtp' => false,
            'read' => false,
            'archived' => false,
        ]);

        Notification::create([
            'company_id' => 1,
            'title' => 'Scheduled Notification',
            'message' => 'This is a scheduled notification.',
            'user_id' => null,
            'role_id' => null,
            'scheduled_at' => Carbon::now()->addDay(),
            'sent_at' => null,
            'status' => 'scheduled',
            'send_via_smtp' => true,
            'read' => false,
            'archived' => false,
        ]);

        Notification::create([
            'company_id' => 1,
            'title' => 'Role-based Notification',
            'message' => 'This notification is for a specific role.',
            'user_id' => null,
            'role_id' => 2, // Assuming role ID 2 exists
            'scheduled_at' => null,
            'sent_at' => Carbon::now(),
            'status' => 'sent',
            'send_via_smtp' => false,
            'read' => false,
            'archived' => false,
        ]);

        Notification::create([
            'company_id' => 1,
            'title' => 'User-specific Notification',
            'message' => 'This notification is for a specific user.',
            'user_id' => 1, // Assuming user ID 1 exists
            'role_id' => null,
            'scheduled_at' => null,
            'sent_at' => Carbon::now(),
            'status' => 'sent',
            'send_via_smtp' => true,
            'read' => false,
            'archived' => false,
        ]);
    }
}
