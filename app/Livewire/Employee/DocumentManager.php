<?php

namespace App\Livewire\Employee;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;

class DocumentManager extends Component
{
    use WithPagination, WithFileUploads;

    public string $search     = '';
    public string $filterType = '';
    public string $filterEmployee = '';

    // Upload modal
    public bool   $showModal     = false;
    public bool   $isEditing     = false;
    public ?int   $editingId     = null;
    public int    $employee_id   = 0;
    public string $docType       = 'other';
    public string $docTitle      = '';
    public string $expires_at    = '';
    public        $file          = null;

    protected $queryString = [
        'search'         => ['except' => ''],
        'filterType'     => ['except' => ''],
        'filterEmployee' => ['except' => ''],
    ];

    public function updatingSearch(): void { $this->resetPage(); }

    public function openUploadModal(int $employeeId = 0): void
    {
        $this->resetForm();
        $this->employee_id = $employeeId;
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $doc = EmployeeDocument::findOrFail($id);
        $this->editingId   = $id;
        $this->isEditing   = true;
        $this->employee_id = $doc->employee_id;
        $this->docType     = $doc->type;
        $this->docTitle    = $doc->title;
        $this->expires_at  = $doc->expires_at?->format('Y-m-d') ?? '';
        $this->file        = null;
        $this->showModal   = true;
    }

    public function save(): void
    {
        $rules = [
            'employee_id' => 'required|integer|exists:employees,id',
            'docType'     => 'required|in:contract,rg,cpf,diploma,medical,certification,other',
            'docTitle'    => 'required|string|max:255',
            'expires_at'  => 'nullable|date',
        ];

        if (!$this->isEditing) {
            $rules['file'] = 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx';
        } else {
            $rules['file'] = 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx';
        }

        $this->validate($rules);

        if ($this->isEditing) {
            $doc = EmployeeDocument::findOrFail($this->editingId);

            $updateData = [
                'type'       => $this->docType,
                'title'      => $this->docTitle,
                'expires_at' => $this->expires_at ?: null,
            ];

            if ($this->file) {
                // Delete old file
                if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                    Storage::disk('public')->delete($doc->file_path);
                }
                $path = $this->file->store('documents', 'public');
                $updateData['file_path']  = $path;
                $updateData['mime_type']  = $this->file->getMimeType();
                $updateData['file_size']  = $this->file->getSize();
            }

            $doc->update($updateData);
            $msg = 'Documento atualizado.';
        } else {
            $path = $this->file->store('documents', 'public');
            EmployeeDocument::create([
                'employee_id' => $this->employee_id,
                'type'        => $this->docType,
                'title'       => $this->docTitle,
                'file_path'   => $path,
                'mime_type'   => $this->file->getMimeType(),
                'file_size'   => $this->file->getSize(),
                'expires_at'  => $this->expires_at ?: null,
                'uploaded_by' => auth()->id(),
            ]);
            $msg = 'Documento enviado com sucesso!';
        }

        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('toast', type: 'success', message: $msg);
    }

    public function deleteDocument(int $id): void
    {
        $doc = EmployeeDocument::findOrFail($id);
        if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
            Storage::disk('public')->delete($doc->file_path);
        }
        $doc->delete();
        $this->dispatch('toast', type: 'success', message: 'Documento excluído.');
    }

    private function resetForm(): void
    {
        $this->employee_id = 0;
        $this->docType     = 'other';
        $this->docTitle    = '';
        $this->expires_at  = '';
        $this->file        = null;
        $this->editingId   = null;
        $this->resetErrorBag();
    }

    public function render()
    {
        $tenantId = auth()->user()->tenant_id;

        $documents = EmployeeDocument::with(['employee.user', 'uploader'])
            ->whereHas('employee', function ($q) use ($tenantId) {
                $q->when($tenantId, fn($sub) => $sub->where('tenant_id', $tenantId));
            })
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterEmployee, fn($q) => $q->where('employee_id', $this->filterEmployee))
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->orderByDesc('created_at')
            ->paginate(20);

        $employees = Employee::with('user')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'active')
            ->orderByRaw("(SELECT name FROM users WHERE users.id = employees.user_id)")
            ->get();

        return view('livewire.employee.document-manager', compact('documents', 'employees'))
            ->layout('layouts.app', ['title' => 'Documentos']);
    }
}
