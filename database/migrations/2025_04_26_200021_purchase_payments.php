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
        Schema::create('purchase_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained('property_purchase_requests')->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['bank_transfer', 'cash', 'credit_card']);
            $table->timestamp('payment_date')->useCurrent();
            $table->string('payment_proof');
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * التراجع عن الترحيل
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_payments');
    }
};