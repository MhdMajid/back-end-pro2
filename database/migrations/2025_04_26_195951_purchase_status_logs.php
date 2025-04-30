<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * تنفيذ الترحيل
     */
    public function up(): void
    {
        Schema::create('purchase_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained('property_purchase_requests')->onDelete('cascade');
            $table->enum('status', ['pending', 'approved', 'in_progress', 'completed', 'rejected', 'cancelled']);
            $table->text('notes')->nullable();
            $table->foreignId('changed_by')->constrained('users')->onDelete('cascade');
            $table->json('documents')->nullable();
            $table->timestamps();
        });
    }

    /**
     * التراجع عن الترحيل
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_status_logs');
    }
};