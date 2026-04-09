<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ─────────────────────────────────────────────────────────────────────────────
// 0010 – JOB OPENINGS (ATS)
// ─────────────────────────────────────────────────────────────────────────────
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_openings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_position_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->enum('type', ['internal', 'external', 'both'])->default('internal');
            $table->enum('mode', ['onsite', 'remote', 'hybrid'])->default('onsite');
            $table->enum('status', ['draft', 'open', 'paused', 'closed'])->default('draft');
            $table->unsignedSmallInteger('vacancies')->default(1);
            $table->date('deadline')->nullable();
            $table->decimal('salary_offered', 12, 2)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_openings');
    }
};
