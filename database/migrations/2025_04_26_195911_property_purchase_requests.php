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
        Schema::create('property_purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->decimal('down_payment_amount', 12, 2);
            $table->decimal('total_amount', 12, 2);
            $table->enum('payment_method', ['bank_transfer', 'cash', 'credit_card']);
            $table->enum('status', ['pending', 'approved', 'in_progress', 'completed', 'rejected', 'cancelled'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->text('buyer_notes')->nullable();
            $table->string('payment_proof')->nullable();
            $table->timestamp('completion_date')->nullable();
            $table->json('legal_documents')->nullable();
            $table->timestamps();
        });
    }

    /**
     * التراجع عن الترحيل
     */
    public function down(): void
    {
        Schema::dropIfExists('property_purchase_requests');
    }
};