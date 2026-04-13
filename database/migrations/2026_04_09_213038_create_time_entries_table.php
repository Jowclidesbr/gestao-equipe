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
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->enum('type', ['clock_in', 'break_out', 'break_in', 'clock_out']);
            $table->time('time');
            $table->string('note', 255)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'date']);
            $table->index(['tenant_id', 'date']);
        });

        Schema::create('time_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->integer('worked_minutes')->default(0);
            $table->integer('expected_minutes')->default(480);
            $table->integer('overtime_minutes')->default(0);
            $table->integer('deficit_minutes')->default(0);
            $table->integer('break_minutes')->default(0);
            $table->enum('status', ['complete', 'incomplete', 'absent', 'holiday', 'day_off'])->default('incomplete');
            $table->timestamps();

            $table->unique(['employee_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_summaries');
        Schema::dropIfExists('time_entries');
    }
};
