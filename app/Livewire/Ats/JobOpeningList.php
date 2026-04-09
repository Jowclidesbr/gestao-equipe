<?php

namespace App\Livewire\Ats;

use App\Models\Department;
use App\Models\JobCandidate;
use App\Models\JobOpening;
use App\Models\JobPosition;
use App\Models\Tenant;
use Livewire\Component;
use Livewire\WithPagination;

class JobOpeningList extends Component
{
    use WithPagination;

    // ── Filters ───────────────────────────────────────────────────────────────
    public string $search        = '';
    public string $filterStatus  = '';
    public string $filterDept    = '';

    // ── Opening modal ─────────────────────────────────────────────────────────
    public bool   $showModal     = false;
    public bool   $isEditing     = false;
    public ?int   $editingId     = null;

    public string $title          = '';
    public string $description    = '';
    public string $requirements   = '';
    public int    $department_id  = 0;
    public int    $job_position_id = 0;
    public string $type           = 'internal';
    public string $mode           = 'onsite';
    public string $status         = 'draft';
    public int    $vacancies      = 1;
    public string $deadline       = '';
    public string $salary_offered = '';
    public ?int   $selected_tenant_id = null;

    // ── Candidates panel ──────────────────────────────────────────────────────
    public bool   $showCandidates    = false;
    public ?int   $activeOpeningId   = null;
    public string $activeOpeningTitle = '';

    // ── Candidate modal ───────────────────────────────────────────────────────
    public bool   $showCandidateModal = false;
    public bool   $editingCandidate   = false;
    public ?int   $editingCandidateId = null;

    public string $cName      = '';
    public string $cEmail     = '';
    public string $cPhone     = '';
    public string $cLinkedin  = '';
    public string $cStatus    = 'applied';
    public string $cNotes     = '';

    protected $queryString = [
        'search'       => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'filterDept'   => ['except' => ''],
    ];

    public function updatingSearch(): void { $this->resetPage(); }

    // ── Opening CRUD ──────────────────────────────────────────────────────────

    public function openCreateModal(): void
    {
        $this->resetOpeningForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $opening = JobOpening::findOrFail($id);

        $this->editingId        = $id;
        $this->isEditing        = true;
        $this->title            = $opening->title;
        $this->description      = $opening->description ?? '';
        $this->requirements     = $opening->requirements ?? '';
        $this->department_id    = $opening->department_id;
        $this->job_position_id  = $opening->job_position_id;
        $this->type             = $opening->type;
        $this->mode             = $opening->mode;
        $this->status           = $opening->status;
        $this->vacancies        = $opening->vacancies;
        $this->deadline         = $opening->deadline?->format('Y-m-d') ?? '';
        $this->salary_offered   = $opening->salary_offered ? (string) $opening->salary_offered : '';
        $this->selected_tenant_id = $opening->tenant_id;
        $this->showModal        = true;
    }

    public function saveOpening(): void
    {
        $user     = auth()->user();
        $tenantId = $user->tenant_id ?? $this->selected_tenant_id;

        $isSuperAdmin = $user->hasRole('super_admin');

        $this->validate([
            'title'              => 'required|string|max:255',
            'department_id'      => 'required|integer|min:1',
            'job_position_id'    => 'required|integer|min:1',
            'type'               => 'required|in:internal,external,both',
            'mode'               => 'required|in:onsite,remote,hybrid',
            'status'             => 'required|in:draft,open,paused,closed',
            'vacancies'          => 'required|integer|min:1|max:999',
            'deadline'           => 'nullable|date',
            'salary_offered'     => 'nullable|numeric|min:0',
            'selected_tenant_id' => $isSuperAdmin ? 'required|integer|exists:tenants,id' : 'nullable',
        ], [
            'selected_tenant_id.required' => 'Selecione um tenant para a vaga.',
        ]);

        $data = [
            'title'           => $this->title,
            'description'     => $this->description ?: null,
            'requirements'    => $this->requirements ?: null,
            'department_id'   => $this->department_id,
            'job_position_id' => $this->job_position_id,
            'type'            => $this->type,
            'mode'            => $this->mode,
            'status'          => $this->status,
            'vacancies'       => $this->vacancies,
            'deadline'        => $this->deadline ?: null,
            'salary_offered'  => $this->salary_offered !== '' ? $this->salary_offered : null,
        ];

        if ($this->isEditing) {
            JobOpening::findOrFail($this->editingId)->update($data);
            $msg = 'Vaga atualizada com sucesso!';
        } else {
            JobOpening::create(array_merge($data, [
                'tenant_id'  => $tenantId,
                'created_by' => auth()->id(),
            ]));
            $msg = 'Vaga criada com sucesso!';
        }

        $this->showModal = false;
        $this->resetOpeningForm();
        $this->dispatch('toast', type: 'success', message: $msg);
    }

    public function deleteOpening(int $id): void
    {
        $opening = JobOpening::findOrFail($id);

        if ($opening->candidates()->count() > 0) {
            $this->dispatch('toast', type: 'error', message: 'Não é possível excluir: vaga possui candidatos cadastrados.');
            return;
        }

        $opening->delete();
        $this->dispatch('toast', type: 'success', message: 'Vaga excluída.');
    }

    private function resetOpeningForm(): void
    {
        $this->title = $this->description = $this->requirements = $this->deadline = $this->salary_offered = '';
        $this->department_id   = 0;
        $this->job_position_id = 0;
        $this->type      = 'internal';
        $this->mode      = 'onsite';
        $this->status    = 'draft';
        $this->vacancies = 1;
        $this->editingId = null;
        $this->selected_tenant_id = null;
        $this->resetErrorBag();
    }

    // ── Candidates panel ──────────────────────────────────────────────────────

    public function viewCandidates(int $openingId): void
    {
        $opening = JobOpening::findOrFail($openingId);
        $this->activeOpeningId    = $openingId;
        $this->activeOpeningTitle = $opening->title;
        $this->showCandidates     = true;
    }

    public function closeCandidates(): void
    {
        $this->showCandidates   = false;
        $this->activeOpeningId  = null;
        $this->showCandidateModal = false;
        $this->resetCandidateForm();
    }

    // ── Candidate CRUD ────────────────────────────────────────────────────────

    public function openAddCandidate(): void
    {
        $this->resetCandidateForm();
        $this->editingCandidate = false;
        $this->showCandidateModal = true;
    }

    public function openEditCandidate(int $id): void
    {
        $c = JobCandidate::findOrFail($id);
        $this->editingCandidateId = $id;
        $this->editingCandidate   = true;
        $this->cName     = $c->name;
        $this->cEmail    = $c->email;
        $this->cPhone    = $c->phone ?? '';
        $this->cLinkedin = $c->linkedin_url ?? '';
        $this->cStatus   = $c->status;
        $this->cNotes    = $c->notes ?? '';
        $this->showCandidateModal = true;
    }

    public function saveCandidate(): void
    {
        $this->validate([
            'cName'     => 'required|string|max:255',
            'cEmail'    => 'required|email|max:255',
            'cPhone'    => 'nullable|string|max:20',
            'cLinkedin' => 'nullable|url|max:255',
            'cStatus'   => 'required|in:applied,screening,interview,technical,offer,hired,rejected',
            'cNotes'    => 'nullable|string|max:2000',
        ], [], [
            'cName'  => 'nome',
            'cEmail' => 'e-mail',
            'cStatus'=> 'status',
        ]);

        $data = [
            'name'         => $this->cName,
            'email'        => $this->cEmail,
            'phone'        => $this->cPhone ?: null,
            'linkedin_url' => $this->cLinkedin ?: null,
            'status'       => $this->cStatus,
            'notes'        => $this->cNotes ?: null,
        ];

        if ($this->editingCandidate) {
            JobCandidate::findOrFail($this->editingCandidateId)->update($data);
            $msg = 'Candidato atualizado.';
        } else {
            JobCandidate::create(array_merge($data, ['job_opening_id' => $this->activeOpeningId]));
            $msg = 'Candidato adicionado.';
        }

        $this->showCandidateModal = false;
        $this->resetCandidateForm();
        $this->dispatch('toast', type: 'success', message: $msg);
    }

    public function advanceCandidate(int $id): void
    {
        $stages = ['applied', 'screening', 'interview', 'technical', 'offer', 'hired'];
        $c = JobCandidate::findOrFail($id);
        $idx = array_search($c->status, $stages);
        if ($idx !== false && $idx < count($stages) - 1) {
            $c->update(['status' => $stages[$idx + 1]]);
            $this->dispatch('toast', type: 'success', message: 'Candidato avançado.');
        }
    }

    public function rejectCandidate(int $id): void
    {
        JobCandidate::findOrFail($id)->update(['status' => 'rejected']);
        $this->dispatch('toast', type: 'success', message: 'Candidato rejeitado.');
    }

    public function deleteCandidate(int $id): void
    {
        JobCandidate::findOrFail($id)->delete();
        $this->dispatch('toast', type: 'success', message: 'Candidato removido.');
    }

    private function resetCandidateForm(): void
    {
        $this->cName = $this->cEmail = $this->cPhone = $this->cLinkedin = $this->cNotes = '';
        $this->cStatus            = 'applied';
        $this->editingCandidateId = null;
        $this->resetErrorBag();
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $user     = auth()->user();
        $tenantId = $user->tenant_id;

        $openings = JobOpening::with(['department', 'jobPosition'])
            ->withCount('candidates')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterDept, fn($q) => $q->where('department_id', $this->filterDept))
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->orderByRaw("FIELD(status, 'open', 'paused', 'draft', 'closed')")
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $modalTenantId = $tenantId ?? $this->selected_tenant_id;

        $departments  = Department::when($modalTenantId, fn($q) => $q->where('tenant_id', $modalTenantId))
            ->where('is_active', true)->orderBy('name')->get();

        $jobPositions = JobPosition::when($modalTenantId, fn($q) => $q->where('tenant_id', $modalTenantId))
            ->where('is_active', true)->orderBy('title')->get();

        $candidates = $this->activeOpeningId
            ? JobCandidate::where('job_opening_id', $this->activeOpeningId)
                ->orderByRaw("FIELD(status,'applied','screening','interview','technical','offer','hired','rejected')")
                ->orderBy('created_at')
                ->get()
            : collect();

        $tenants = $user->hasRole('super_admin')
            ? Tenant::where('is_active', true)->orderBy('name')->get()
            : collect();

        return view('livewire.ats.job-opening-list', compact('openings', 'departments', 'jobPositions', 'candidates', 'tenants'))
            ->layout('layouts.app', ['title' => 'Vagas (ATS)']);
    }
}
