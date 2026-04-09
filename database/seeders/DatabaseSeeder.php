<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\JobPosition;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Roles ─────────────────────────────────────────────────────────────
        $roles = ['super_admin', 'admin', 'manager', 'employee'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // ── SuperAdmin (no tenant) ─────────────────────────────────────────────
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@gestaoequipe.com'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('password'),
                'is_active'=> true,
            ]
        );
        $superAdmin->assignRole('super_admin');

        // ── Demo Tenant ───────────────────────────────────────────────────────
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'demo'],
            [
                'name'          => 'Banco Demo S.A.',
                'cnpj'          => '00.000.000/0001-00',
                'primary_color' => '#EC0000',
                'email'         => 'rh@banco-demo.com',
                'is_active'     => true,
            ]
        );

        // ── Departments ───────────────────────────────────────────────────────
        $deptRH  = Department::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Recursos Humanos'],
            ['code' => 'RH', 'is_active' => true]
        );
        $deptTI  = Department::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Tecnologia da Informação'],
            ['code' => 'TI', 'is_active' => true]
        );
        $deptFin = Department::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Financeiro'],
            ['code' => 'FIN', 'is_active' => true]
        );

        // ── Job Positions ─────────────────────────────────────────────────────
        $posGerente = JobPosition::firstOrCreate(
            ['tenant_id' => $tenant->id, 'title' => 'Gerente de RH'],
            ['department_id' => $deptRH->id, 'level' => 'manager', 'is_active' => true]
        );
        $posDev = JobPosition::firstOrCreate(
            ['tenant_id' => $tenant->id, 'title' => 'Desenvolvedor Full Stack'],
            ['department_id' => $deptTI->id, 'level' => 'analyst', 'is_active' => true]
        );
        $posAnalista = JobPosition::firstOrCreate(
            ['tenant_id' => $tenant->id, 'title' => 'Analista RH'],
            ['department_id' => $deptRH->id, 'level' => 'analyst', 'is_active' => true]
        );

        // ── Admin user ────────────────────────────────────────────────────────
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'tenant_id' => $tenant->id,
                'name'      => 'Ana Souza',
                'password'  => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $adminUser->assignRole('admin');
        Employee::firstOrCreate(
            ['user_id' => $adminUser->id],
            [
                'tenant_id'       => $tenant->id,
                'department_id'   => $deptRH->id,
                'job_position_id' => $posGerente->id,
                'admission_date'  => Carbon::parse('2019-03-01'),
                'contract_type'   => 'clt',
                'work_mode'       => 'hybrid',
                'status'          => 'active',
                'vacation_balance_days' => 30,
            ]
        );

        // ── Manager user ──────────────────────────────────────────────────────
        $managerUser = User::firstOrCreate(
            ['email' => 'manager@demo.com'],
            [
                'tenant_id' => $tenant->id,
                'name'      => 'Carlos Mendes',
                'password'  => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $managerUser->assignRole('manager');
        $managerEmployee = Employee::firstOrCreate(
            ['user_id' => $managerUser->id],
            [
                'tenant_id'       => $tenant->id,
                'department_id'   => $deptTI->id,
                'job_position_id' => $posDev->id,
                'admission_date'  => Carbon::parse('2020-07-15'),
                'contract_type'   => 'clt',
                'work_mode'       => 'remote',
                'status'          => 'active',
                'vacation_balance_days' => 25,
            ]
        );

        // ── Employee user ─────────────────────────────────────────────────────
        $empUser = User::firstOrCreate(
            ['email' => 'funcionario@demo.com'],
            [
                'tenant_id' => $tenant->id,
                'name'      => 'João Pereira',
                'password'  => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $empUser->assignRole('employee');
        Employee::firstOrCreate(
            ['user_id' => $empUser->id],
            [
                'tenant_id'       => $tenant->id,
                'department_id'   => $deptTI->id,
                'job_position_id' => $posDev->id,
                'manager_id'      => $managerEmployee->id,
                'admission_date'  => Carbon::parse('2022-01-10'),
                'birth_date'      => Carbon::parse('1995-' . now()->format('m') . '-' . now()->format('d')),
                'contract_type'   => 'clt',
                'work_mode'       => 'onsite',
                'status'          => 'active',
                'vacation_balance_days' => 30,
            ]
        );

        $this->command->info('✅ Seeder concluído. Credenciais de acesso:');
        $this->command->table(
            ['Role', 'E-mail', 'Senha'],
            [
                ['SuperAdmin',  'superadmin@gestaoequipe.com', 'password'],
                ['Admin',       'admin@demo.com',              'password'],
                ['Manager',     'manager@demo.com',            'password'],
                ['Employee',    'funcionario@demo.com',        'password'],
            ]
        );
    }
}
