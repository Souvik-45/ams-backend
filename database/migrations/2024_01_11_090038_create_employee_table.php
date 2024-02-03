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
       // In a migration file (e.g., create_employee_table.php)
    Schema::create('employee', function (Blueprint $table) {
    $table->String('employee_id');
    $table->string('employee_name');
    $table->string('department_name');
    $table->string('job_role');
    // Add other employee-related columns
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee');
    }
};
