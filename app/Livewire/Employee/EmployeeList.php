<?php

namespace App\Livewire\Employee;

use App\Models\Employee;
use App\Models\Department;
use App\Models\JobPosition;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class EmployeeList extends Component
{
    use WithPagination, WithFileUploads;

    public string $search         = '';
    public string $filterDept     = '';
    public string $filterStatus   = 'active';
    public bool   $showModal      = false;
    public bool   $isEditing      = false;
    public ?int   $editingId      = null;

    // Form fields
    public string $name           = '';
    public string $email          = '';
    public string $employee_code  = '';
    public string $cpf            = '';
    public string $birth_date     = '';
    public string $admission_date = '';
    public string $contract_type  = 'clt';
    public string $work_mode      = 'onsite';
    public string $shift          = '';
    public string $team           = '';
    public int    $department_id  = 0;
    public int    $job_position_id = 0;
    public string $status         = 'active';
    public string $phone          = '';
    public string $mobile         = '';
    public array  $presence_days  = [];
    public        $photo          = null;   // Livewire temp upload
    public ?int   $selected_tenant_id = null;

    protected $queryString = [
        'search'       => ['except' => ''],
        'filterDept'   => ['except' => ''],
        'filterStatus' => ['except' => 'active'],
    ];

    public function updatingSearch(): void { $this->resetPage(); }

    public function mount(): void
    {
        if (request()->routeIs('admin.employees.create')) {
            $this->openCreateModal();
        }
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->isEditing  = false;
        $this->showModal  = true;
    }

    public function openEditModal(int $id): void
    {
        $employee = Employee::with('user')->findOrFail($id);

        if (!Gate::allows('update', $employee)) {
            $this->dispatch('toast', type: 'error', message: 'Sem permissão para editar este colaborador.');
            return;
        }

        $this->editingId       = $id;
        $this->isEditing       = true;
        $this->name            = $employee->user->name;
        $this->email           = $employee->user->email;
        $this->employee_code   = $employee->employee_code ?? '';
        $this->cpf             = $employee->cpf ?? '';
        $this->birth_date      = $employee->birth_date?->format('Y-m-d') ?? '';
        $this->admission_date  = $employee->admission_date->format('Y-m-d');
        $this->contract_type   = $employee->contract_type;
        $this->work_mode       = $employee->work_mode;
        $this->shift           = $employee->shift ?? '';
        $this->team            = $employee->team ?? '';
        $this->department_id   = $employee->department_id ?? 0;
        $this->job_position_id = $employee->job_position_id ?? 0;
        $this->status          = $employee->status;
        $this->phone           = $employee->phone ?? '';
        $this->mobile          = $employee->mobile ?? '';
        $this->presence_days   = $employee->presence_days ?? [];
        $this->selected_tenant_id = $employee->tenant_id;
        $this->showModal       = true;
    }

    public function save(): void
    {
        $user     = auth()->user();
        $tenantId = $user->tenant_id ?? $this->selected_tenant_id;

        $isSuperAdmin = $user->hasRole('super_admin');

        $rules = [
            'name'               => 'required|string|max:255',
            'email'              => 'required|email|max:255',
            'employee_code'      => 'nullable|string|max:50',
            'cpf'                => 'nullable|string|max:14',
            'birth_date'         => 'nullable|date',
            'admission_date'     => 'required|date',
            'contract_type'      => 'required|in:clt,pj,intern,temporary',
            'work_mode'          => 'required|in:onsite,remote,hybrid',
            'shift'              => 'nullable|in:I,II,III',
            'team'               => 'nullable|in:run_the_bank,change_the_bank',
            'department_id'      => 'required|integer|min:1',
            'job_position_id'    => 'required|integer|min:1',
            'status'             => 'required|in:active,inactive,on_leave,terminated',
            'photo'              => 'nullable|image|max:2048',
            'selected_tenant_id' => $isSuperAdmin ? 'required|integer|exists:tenants,id' : 'nullable',
        ];
        $this->validate($rules, [
            'selected_tenant_id.required' => 'Selecione um tenant para o colaborador.',
        ]);

        DB::transaction(function () use ($tenantId) {
            if ($this->isEditing) {
                $employee = Employee::findOrFail($this->editingId);

                if (!Gate::allows('update', $employee)) {
                    $this->dispatch('toast', type: 'error', message: 'Sem permissão.');
                    return;
                }

                $userUpdate = ['name' => $this->name, 'email' => $this->email];
                if ($this->photo) {
                    $userUpdate['avatar_path'] = $this->photo->store('avatars', 'public');
                }
                $employee->user->update($userUpdate);
                $employee->update([
                    'employee_code'   => $this->employee_code ?: null,
                    'cpf'             => $this->cpf ?: null,
                    'birth_date'      => $this->birth_date ?: null,
                    'admission_date'  => $this->admission_date,
                    'contract_type'   => $this->contract_type,
                    'work_mode'       => $this->work_mode,
                    'shift'           => $this->shift ?: null,
                    'team'            => $this->team ?: null,
                    'presence_days'   => $this->presence_days ?: null,
                    'department_id'   => $this->department_id ?: null,
                    'job_position_id' => $this->job_position_id ?: null,
                    'status'          => $this->status,
                    'phone'           => $this->phone ?: null,
                    'mobile'          => $this->mobile ?: null,
                ]);
            } else {
                if (!Gate::allows('create', Employee::class)) {
                    $this->dispatch('toast', type: 'error', message: 'Sem permissão para criar colaboradores.');
                    return;
                }

                $user = User::create([
                    'tenant_id'   => $tenantId,
                    'name'        => $this->name,
                    'email'       => $this->email,
                    'password'    => Hash::make(\Str::random(16)),
                    'is_active'   => true,
                    'avatar_path' => $this->photo ? $this->photo->store('avatars', 'public') : null,
                ]);
                $user->assignRole('employee');

                Employee::create([
                    'tenant_id'       => $tenantId,
                    'user_id'         => $user->id,
                    'employee_code'   => $this->employee_code ?: null,
                    'cpf'             => $this->cpf ?: null,
                    'birth_date'      => $this->birth_date ?: null,
                    'admission_date'  => $this->admission_date,
                    'contract_type'   => $this->contract_type,
                    'work_mode'       => $this->work_mode,
                    'shift'           => $this->shift ?: null,
                    'team'            => $this->team ?: null,
                    'presence_days'   => $this->presence_days ?: null,
                    'department_id'   => $this->department_id ?: null,
                    'job_position_id' => $this->job_position_id ?: null,
                    'status'          => 'active',
                    'phone'           => $this->phone ?: null,
                    'mobile'          => $this->mobile ?: null,
                ]);
            }
        });

        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('toast', type: 'success', message: $this->isEditing ? 'Colaborador atualizado.' : 'Colaborador criado com sucesso!');
    }

    private function resetForm(): void
    {
        $this->name = $this->email = $this->employee_code = $this->cpf =
        $this->birth_date = $this->admission_date = $this->phone = $this->mobile = '';
        $this->contract_type   = 'clt';
        $this->work_mode       = 'onsite';
        $this->shift           = '';
        $this->team            = '';
        $this->status          = 'active';
        $this->presence_days   = [];
        $this->photo           = null;
        $this->department_id   = 0;
        $this->job_position_id = 0;
        $this->editingId       = null;
        $this->selected_tenant_id = null;
        $this->resetErrorBag();
    }

    public function render()
    {
        $user     = auth()->user();
        $tenantId = $user->tenant_id; // null for super_admin

        $employees = Employee::with(['user', 'department', 'jobPosition'])
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterDept, fn($q) => $q->where('department_id', $this->filterDept))
            ->when($this->search, function ($q) {
                $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$this->search}%")
                                                   ->orWhere('email', 'like', "%{$this->search}%"));
            })
            ->orderBy('id')
            ->paginate(20);

        $modalTenantId = $tenantId ?? $this->selected_tenant_id;

        $departments  = Department::when($modalTenantId, fn($q) => $q->where('tenant_id', $modalTenantId))->where('is_active', true)->get();
        $jobPositions = JobPosition::when($modalTenantId, fn($q) => $q->where('tenant_id', $modalTenantId))->where('is_active', true)->get();

        $tenants = $user->hasRole('super_admin')
            ? Tenant::where('is_active', true)->orderBy('name')->get()
            : collect();

        return view('livewire.employee.employee-list', compact('employees', 'departments', 'jobPositions', 'tenants'))
            ->layout('layouts.app', ['title' => 'Colaboradores']);
    }
}
