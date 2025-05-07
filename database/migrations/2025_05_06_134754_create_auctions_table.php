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
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            $table->foreignId('winner_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('start_price', 12, 2);
            $table->decimal('min_increment', 12, 2)->nullable();
            $table->decimal('reserve_price', 12, 2)->nullable();
            $table->decimal('winning_bid_amount', 12, 2)->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->enum('status', ['pending', 'active', 'ended', 'cancelled' , 'draft'])->default('pending');
            $table->text('description')->nullable();
            $table->text('title')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auctions');
    }
};
