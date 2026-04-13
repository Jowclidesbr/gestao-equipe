<?php

namespace App\Livewire\Absence;

use App\Models\Absence;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class AbsenceManager extends Component
{
    use WithPagination, WithFileUploads;

    public string $search       = '';
    public string $filterType   = '';
    public string $filterStatus = '';

    // Form
    public bool   $showModal       = false;
    public bool   $isEditing       = false;
    public ?int   $editingId       = null;
    public int    $employee_id     = 0;
    public string $type            = 'sick_leave';
    public string $start_date      = '';
    public string $end_date        = '';
    public string $notes           = '';
    public string $cid_code        = '';
    public        $document        = null; // Livewire temp upload
    public ?string $existingDocPath = null;

    // Detail
    public bool $showDetail   = false;
    public ?Absence $viewing  = null;

    protected $queryString = [
        'search'       => ['except' => ''],
        'filterType'   => ['except' => ''],
        'filterStatus' => ['except' => ''],
    ];

    public function updatingSearch(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $absence = Absence::findOrFail($id);
        $this->editingId      = $id;
        $this->isEditing      = true;
        $this->employee_id    = $absence->employee_id;
        $this->type           = $absence->type;
        $this->start_date     = $absence->start_date->format('Y-m-d');
        $this->end_date       = $absence->end_date->format('Y-m-d');
        $this->notes          = $absence->notes ?? '';
        $this->cid_code       = $absence->cid_code ?? '';
        $this->existingDocPath = $absence->document_path;
        $this->document       = null;
        $this->showModal      = true;
    }

    public function save(): void
    {
        $this->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'type'        => 'required|in:sick_leave,accident,maternity,paternity,bereavement,jury_duty,unpaid,other',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'notes'       => 'nullable|string|max:2000',
            'cid_code'    => 'nullable|string|max:10',
            'document'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $user     = auth()->user();
        $tenantId = $user->tenant_id ?? Employee::find($this->employee_id)?->tenant_id;

        $docPath = $this->existingDocPath;
        if ($this->document) {
            $docPath = $this->document->store('absences', 'public');
        }

        $data = [
            'tenant_id'     => $tenantId,
            'employee_id'   => $this->employee_id,
            'registered_by' => $user->id,
            'type'          => $this->type,
            'start_date'    => $this->start_date,
            'end_date'      => $this->end_date,
            'notes'         => $this->notes ?: null,
            'cid_code'      => $this->cid_code ?: null,
            'document_path' => $docPath,
        ];

        if ($this->isEditing) {
            Absence::findOrFail($this->editingId)->update($data);
            $msg = 'Afastamento atualizado.';
        } else {
            Absence::create($data);
            $msg = 'Afastamento registrado.';
        }

        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('toast', type: 'success', message: $msg);
    }

    public function viewDetail(int $id): void
    {
        $this->viewing   = Absence::with('employee.user', 'employee.department', 'registeredBy')->findOrFail($id);
        $this->showDetail = true;
    }

    public function deleteAbsence(int $id): void
    {
        $absence = Absence::findOrFail($id);
        if ($absence->document_path) {
            Storage::disk('public')->delete($absence->document_path);
        }
        $absence->delete();
        $this->dispatch('toast', type: 'success', message: 'Afastamento removido.');
    }

    public function approve(int $id): void
    {
        $absence = Absence::findOrFail($id);
        $absence->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        $this->dispatch('toast', type: 'success', message: 'Afastamento aprovado.');
    }

    public function reject(int $id): void
    {
        $absence = Absence::findOrFail($id);
        $absence->update([
            'status'      => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        $this->dispatch('toast', type: 'warning', message: 'Afastamento rejeitado.');
    }

    public function downloadDocument(int $id): mixed
    {
        $absence = Absence::findOrFail($id);
        if (!$absence->document_path || !Storage::disk('public')->exists($absence->document_path)) {
            $this->dispatch('toast', type: 'error', message: 'Documento não encontrado.');
            return null;
        }
        return Storage::disk('public')->download($absence->document_path);
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->employee_id = 0;
        $this->type = 'sick_leave';
        $this->start_date = $this->end_date = $this->notes = $this->cid_code = '';
        $this->document = null;
        $this->existingDocPath = null;
        $this->resetErrorBag();
    }

    public function render()
    {
        $user     = auth()->user();
        $tenantId = $user->tenant_id;

        $absences = Absence::with('employee.user', 'employee.department')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->when($this->filterType,   fn($q) => $q->where('type',   $this->filterType))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->search, fn($q) => $q->whereHas('employee.user', fn($u) => $u->where('name', 'like', "%{$this->search}%")))
            ->orderByDesc('start_date')
            ->paginate(20);

        $employees = Employee::with('user')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'active')
            ->get()
            ->sortBy(fn($e) => $e->user->name);

        return view('livewire.absence.absence-manager', compact('absences', 'employees'))
            ->layout('layouts.app', ['title' => 'Afastamentos']);
    }
}
