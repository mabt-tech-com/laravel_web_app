<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // null for all users
            $table->foreignId('role_id')->nullable()->constrained()->onDelete('cascade'); // null for all users
            $table->timestamp('scheduled_at')->nullable();                                      // null for immediate
            $table->timestamp('sent_at')->nullable();
            $table->enum('status', ['pending', 'sent', 'failed', 'scheduled'])->default('pending');
            $table->boolean('send_via_smtp')->default(false);                            // false for in-app only
            $table->boolean('read')->default(false);
            $table->boolean('archived')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }



    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}
