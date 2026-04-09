<?php

namespace App\Livewire\Vacation;

use App\Models\VacationRequest;
use App\Services\VacationApprovalService;
use Livewire\Component;
use Livewire\WithPagination;

class VacationRequestList extends Component
{
    use WithPagination;

    public string $search     = '';
    public string $filterStatus = '';

    // ─── Modal state ──────────────────────────────────────────────────────────
    public bool $showRejectModal  = false;
    public ?int $rejectingId      = null;
    public string $rejectNotes    = '';

    protected $queryString = [
        'search'        => ['except' => ''],
        'filterStatus'  => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function approve(int $id, VacationApprovalService $service): void
    {
        $request = VacationRequest::findOrFail($id);
        $this->authorize('approve', $request);

        try {
            $service->approve($request, auth()->user());
            $this->dispatch('toast', type: 'success', message: 'Férias aprovadas com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('toast', type: 'error', message: collect($e->errors())->flatten()->first());
        }
    }

    public function openRejectModal(int $id): void
    {
        $this->rejectingId   = $id;
        $this->rejectNotes   = '';
        $this->showRejectModal = true;
    }

    public function confirmReject(VacationApprovalService $service): void
    {
        $this->validate(['rejectNotes' => 'required|min:10']);

        $request = VacationRequest::findOrFail($this->rejectingId);
        $this->authorize('reject', $request);

        try {
            $service->reject($request, auth()->user(), $this->rejectNotes);
            $this->showRejectModal = false;
            $this->dispatch('toast', type: 'success', message: 'Solicitação rejeitada.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('toast', type: 'error', message: collect($e->errors())->flatten()->first());
        }
    }

    public function render()
    {
        $user   = auth()->user();
        $query  = VacationRequest::with(['employee.user', 'employee.department'])
            ->where('tenant_id', $user->tenant_id);

        // Managers only see their team
        if ($user->isManager()) {
            $managerEmployeeId = $user->employee?->id;
            $query->whereHas('employee', function ($q) use ($managerEmployeeId) {
                $q->where('manager_id', $managerEmployeeId)
                  ->orWhere('id', $managerEmployeeId);
            });
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->search) {
            $query->whereHas('employee.user', function ($q) {
                $q->where('name', 'like', "%{$this->search}%");
            });
        }

        $requests = $query->orderByDesc('submitted_at')->paginate(15);

        return view('livewire.vacation.vacation-request-list', compact('requests'))
            ->layout('layouts.app', ['title' => 'Solicitações de Férias']);
    }
}
