<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('position')->nullable();
            $table->string('avatar')->nullable();
            $table->json('working_hours')->nullable(); // { mon: [{start,end}], tue:[...] }
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('employee_service', function (Blueprint $table) {
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->primary(['employee_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_service');
        Schema::dropIfExists('employees');
    }
};
