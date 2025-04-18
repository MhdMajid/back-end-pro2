<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 12, 2);
            $table->enum('type', ['sale', 'rent']); // بيع أو إيجار
            $table->string('location');
            $table->string('address');
            $table->integer('floor_number')->nullable();
            $table->integer('rooms')->default(1);
            $table->integer('bathrooms')->default(1);
            $table->decimal('area', 10, 2); // المساحة بالمتر المربع
            $table->json('additional_conditions')->nullable(); // شروط إضافية
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });

        // جدول لصور العقار
        Schema::create('property_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->string('image_path');
            $table->boolean('is_main')->default(false); // الصورة الرئيسية
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_images');
        Schema::dropIfExists('properties');
    }
};