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
        // In a migration file (e.g., create_attendance_table.php)
    Schema::create('attendance', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users');
    $table->date('date');
    $table->timestamp('time_in');
    $table->timestamp('time_out')->nullable();
    $table->string('location')->nullable();
    $table->string('status');
    $table->string('image_url')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
