<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ─────────────────────────────────────────────────────────────────────────────
// 0005 – EMPLOYEES (HR profile linked to a user)
// ─────────────────────────────────────────────────────────────────────────────
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('job_position_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('employees')->nullOnDelete();

            // Personal data
            $table->string('employee_code', 50)->nullable();
            $table->string('cpf', 14)->nullable();
            $table->string('rg', 20)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female', 'non_binary', 'not_informed'])->default('not_informed');
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 2)->nullable();
            $table->string('cep', 9)->nullable();

            // Contract data
            $table->date('admission_date');
            $table->date('dismissal_date')->nullable();
            $table->enum('contract_type', ['clt', 'pj', 'intern', 'temporary'])->default('clt');
            $table->enum('work_mode', ['onsite', 'remote', 'hybrid'])->default('onsite');
            $table->decimal('salary', 12, 2)->nullable();
            $table->enum('status', ['active', 'inactive', 'on_leave', 'terminated'])->default('active');

            // Vacation control
            $table->integer('vacation_balance_days')->default(30)
                  ->comment('Current available vacation days');
            $table->date('vacation_acquisition_start')->nullable()
                  ->comment('Start of current acquisition period');
            $table->date('vacation_acquisition_end')->nullable();
            $table->date('vacation_concession_end')->nullable()
                  ->comment('Last day vacation can be taken (acquisition + 12 months)');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'department_id']);
            $table->unique(['tenant_id', 'employee_code']);
            $table->unique(['tenant_id', 'cpf']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
