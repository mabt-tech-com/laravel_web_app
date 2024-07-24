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
