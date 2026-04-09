<?php

namespace App\Livewire\Department;

use App\Models\Department;
use App\Models\Tenant;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class DepartmentList extends Component
{
    use WithPagination;

    public string $search    = '';
    public bool   $showModal = false;
    public bool   $isEditing = false;
    public ?int   $editingId = null;

    // Form fields
    public string $name              = '';
    public string $code              = '';
    public string $description       = '';
    public int    $parent_id         = 0;
    public bool   $is_active         = true;
    public ?int   $selected_tenant_id = null;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatingSearch(): void { $this->resetPage(); }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $dept = Department::findOrFail($id);

        $this->editingId          = $id;
        $this->isEditing          = true;
        $this->name               = $dept->name;
        $this->code               = $dept->code ?? '';
        $this->description        = $dept->description ?? '';
        $this->parent_id          = $dept->parent_id ?? 0;
        $this->is_active          = $dept->is_active;
        $this->selected_tenant_id = $dept->tenant_id;
        $this->showModal          = true;
    }

    public function save(): void
    {
        $user     = auth()->user();
        $tenantId = $user->tenant_id ?? $this->selected_tenant_id;

        $isSuperAdmin = $user->hasRole('super_admin');

        $this->validate([
            'name'               => 'required|string|max:255',
            'code'               => 'nullable|string|max:20',
            'description'        => 'nullable|string|max:1000',
            'parent_id'          => 'nullable|integer|min:0',
            'is_active'          => 'boolean',
            'selected_tenant_id' => $isSuperAdmin ? 'required|integer|exists:tenants,id' : 'nullable',
        ], [
            'selected_tenant_id.required' => 'Selecione um tenant para o departamento.',
        ]);

        $data = [
            'name'        => $this->name,
            'code'        => $this->code ?: null,
            'description' => $this->description ?: null,
            'parent_id'   => $this->parent_id ?: null,
            'is_active'   => $this->is_active,
        ];

        if ($this->isEditing) {
            $dept = Department::findOrFail($this->editingId);
            $dept->update($data);
            $message = 'Departamento atualizado com sucesso!';
        } else {
            Department::create(array_merge($data, ['tenant_id' => $tenantId]));
            $message = 'Departamento criado com sucesso!';
        }

        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('toast', type: 'success', message: $message);
    }

    public function toggleStatus(int $id): void
    {
        $dept = Department::findOrFail($id);
        $dept->update(['is_active' => !$dept->is_active]);
        $this->dispatch('toast', type: 'success', message: 'Status atualizado.');
    }

    public function delete(int $id): void
    {
        $dept = Department::findOrFail($id);

        if ($dept->employees()->count() > 0) {
            $this->dispatch('toast', type: 'error', message: 'Não é possível excluir: departamento possui colaboradores.');
            return;
        }

        $dept->delete();
        $this->dispatch('toast', type: 'success', message: 'Departamento excluído.');
    }

    private function resetForm(): void
    {
        $this->name               = '';
        $this->code               = '';
        $this->description        = '';
        $this->parent_id          = 0;
        $this->is_active          = true;
        $this->editingId          = null;
        $this->selected_tenant_id = null;
        $this->resetErrorBag();
    }

    public function render()
    {
        $user     = auth()->user();
        $tenantId = $user->tenant_id;

        // For parent options in the modal, super_admin filters by the selected tenant
        $modalTenantId = $tenantId ?? $this->selected_tenant_id;

        $departments = Department::with('parent')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->when($this->search, fn($q) => $q->where(function ($sub) {
                $sub->where('name', 'like', "%{$this->search}%")
                    ->orWhere('code', 'like', "%{$this->search}%");
            }))
            ->orderBy('name')
            ->paginate(15);

        $parentOptions = Department::when($modalTenantId, fn($q) => $q->where('tenant_id', $modalTenantId))
            ->where('is_active', true)
            ->when($this->editingId, fn($q) => $q->where('id', '!=', $this->editingId))
            ->orderBy('name')
            ->get();

        $tenants = $user->hasRole('super_admin')
            ? Tenant::where('is_active', true)->orderBy('name')->get()
            : collect();

        return view('livewire.department.department-list', compact('departments', 'parentOptions', 'tenants'))
            ->layout('layouts.app', ['title' => 'Departamentos']);
    }
}
