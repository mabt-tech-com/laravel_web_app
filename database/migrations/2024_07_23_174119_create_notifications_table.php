<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id(); // Creates an auto-incrementing ID column to uniquely identify each notification.

            $table->string('title'); // Defines a 'title' column to store the title of the notification.
            $table->text('message'); // Defines a 'message' column to store the body or content of the notification.

            $table->enum('status', ['pending', 'sent', 'failed', 'scheduled']) // Defines an 'status' column with specific allowable values to track the delivery status of the notification.
            ->default('pending'); // Sets the default status of notifications to 'pending'.

            $table->boolean('read')->default(false); // Defines a 'read' boolean column to indicate whether the notification has been read. Default is false.
            $table->boolean('archived')->default(false); // Defines an 'archived' boolean column to indicate whether the notification has been archived. Default is false.

            $table->unsignedBigInteger('company_id') // Defines a 'company_id' column that stores the ID of the company associated with the notification.
            ->default(1); // Sets the default company ID to 1. This implies every notification belongs to company 1 by default unless specified.
            $table->boolean('send_via_smtp')->default(false); // Defines a 'send_via_smtp' boolean column to specify if the notification should be sent via SMTP/email.

            $table->timestamps(); // Adds nullable created_at and updated_at columns for recording the creation and last update times.
            $table->softDeletes(); // Adds a nullable deleted_at column for soft deletes, allowing the data to be restored if needed.
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};




/*
 * old one :
            $table->id();
            $table->unsignedBigInteger('company_id')->default(1);
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->string('title');
            $table->text('message');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // null for all users
            $table->foreignId('role_id')->nullable()->constrained()->onDelete('cascade'); // null for all users
            $table->timestamp('scheduled_at')->nullable(); // null for immediate
            $table->timestamp('sent_at')->nullable();
            $table->enum('status', ['pending', 'sent', 'failed', 'scheduled'])->default('pending');
            $table->boolean('send_via_smtp')->default(false); // false for in-app only
            $table->boolean('read')->default(false);
            $table->boolean('archived')->default(false);
            $table->softDeletes();
            $table->timestamps();
*/
