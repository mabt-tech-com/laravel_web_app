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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();

            $table->string('code');
            $table->string('title');
            $table->string('description');

            $table->integer('discount_percentage')->nullable();
            $table->decimal('discount_value', 10, 2)->nullable();

            $table->decimal('applicable_if_total_is_above', 10, 3);

            $table->integer('max_usage');

            $table->boolean('active')->default(true);
            $table->date('starts_at');
            $table->date('expires_at');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
