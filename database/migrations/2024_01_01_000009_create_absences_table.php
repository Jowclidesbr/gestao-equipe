<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ─────────────────────────────────────────────────────────────────────────────
// 0009 – ABSENCES / LEAVES (sick leave, INSS, etc.)
// ─────────────────────────────────────────────────────────────────────────────
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('type', [
                'sick_leave', 'accident', 'maternity', 'paternity',
                'bereavement', 'jury_duty', 'unpaid', 'other'
            ]);
            $table->date('start_date');
            $table->date('end_date');
            $table->text('notes')->nullable();
            $table->string('cid_code', 10)->nullable()->comment('CID-10 for medical leaves');
            $table->string('document_path')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'type']);
            $table->index(['employee_id', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absences');
    }
};
