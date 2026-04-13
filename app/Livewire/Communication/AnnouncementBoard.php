<?php

namespace App\Livewire\Communication;

use App\Models\Announcement;
use App\Models\AnnouncementRead;
use App\Models\Department;
use App\Models\Tenant;
use Livewire\Component;
use Livewire\WithPagination;

class AnnouncementBoard extends Component
{
    use WithPagination;

    public string $search       = '';
    public string $filterPriority = '';

    // Create/Edit modal
    public bool   $showModal     = false;
    public bool   $isEditing     = false;
    public ?int   $editingId     = null;
    public string $title         = '';
    public string $body          = '';
    public string $priority      = 'normal';
    public string $audience      = 'all';
    public int    $department_id = 0;
    public string $team_filter   = '';
    public bool   $is_pinned     = false;
    public string $published_at  = '';
    public string $expires_at    = '';
    public ?int   $selected_tenant_id = null;

    // Detail view
    public bool   $showDetail    = false;
    public ?int   $detailId      = null;

    protected $queryString = [
        'search'         => ['except' => ''],
        'filterPriority' => ['except' => ''],
    ];

    public function updatingSearch(): void { $this->resetPage(); }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->published_at = now()->format('Y-m-d\TH:i');
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $a = Announcement::findOrFail($id);
        $this->editingId     = $id;
        $this->isEditing     = true;
        $this->title         = $a->title;
        $this->body          = $a->body;
        $this->priority      = $a->priority;
        $this->audience      = $a->audience;
        $this->department_id = $a->department_id ?? 0;
        $this->team_filter   = $a->team_filter ?? '';
        $this->is_pinned     = $a->is_pinned;
        $this->published_at  = $a->published_at?->format('Y-m-d\TH:i') ?? '';
        $this->expires_at    = $a->expires_at?->format('Y-m-d\TH:i') ?? '';
        $this->showModal     = true;
    }

    public function save(): void
    {
        $this->validate([
            'title'         => 'required|string|max:255',
            'body'          => 'required|string|max:5000',
            'priority'      => 'required|in:normal,important,urgent',
            'audience'      => 'required|in:all,department,team',
            'department_id' => $this->audience === 'department' ? 'required|integer|exists:departments,id' : 'nullable',
            'team_filter'   => $this->audience === 'team' ? 'required|in:run_the_bank,change_the_bank' : 'nullable',
            'published_at'  => 'required|date',
            'expires_at'    => 'nullable|date|after:published_at',
        ]);

        $user = auth()->user();
        $tenantId = $user->tenant_id ?? $this->selected_tenant_id;

        if (!$tenantId) {
            $this->addError('selected_tenant_id', 'Selecione um tenant para publicar o comunicado.');
            return;
        }

        $data = [
            'title'         => $this->title,
            'body'          => $this->body,
            'priority'      => $this->priority,
            'audience'      => $this->audience,
            'department_id' => $this->audience === 'department' ? $this->department_id : null,
            'team_filter'   => $this->audience === 'team' ? $this->team_filter : null,
            'is_pinned'     => $this->is_pinned,
            'published_at'  => $this->published_at,
            'expires_at'    => $this->expires_at ?: null,
        ];

        if ($this->isEditing) {
            Announcement::findOrFail($this->editingId)->update($data);
            $msg = 'Comunicado atualizado.';
        } else {
            Announcement::create(array_merge($data, [
                'tenant_id' => $tenantId,
                'author_id' => $user->id,
            ]));
            $msg = 'Comunicado publicado!';
        }

        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('toast', type: 'success', message: $msg);
    }

    public function deleteAnnouncement(int $id): void
    {
        Announcement::findOrFail($id)->delete();
        $this->dispatch('toast', type: 'success', message: 'Comunicado excluído.');
    }

    public function togglePin(int $id): void
    {
        $a = Announcement::findOrFail($id);
        $a->update(['is_pinned' => !$a->is_pinned]);
        $this->dispatch('toast', type: 'success', message: $a->is_pinned ? 'Fixado.' : 'Desfixado.');
    }

    public function viewDetail(int $id): void
    {
        $this->detailId   = $id;
        $this->showDetail = true;

        // Mark as read
        AnnouncementRead::firstOrCreate(
            ['announcement_id' => $id, 'user_id' => auth()->id()],
            ['read_at' => now()],
        );
    }

    public function closeDetail(): void
    {
        $this->showDetail = false;
        $this->detailId   = null;
    }

    private function resetForm(): void
    {
        $this->title = $this->body = $this->team_filter = $this->published_at = $this->expires_at = '';
        $this->priority      = 'normal';
        $this->audience      = 'all';
        $this->department_id = 0;
        $this->is_pinned     = false;
        $this->editingId     = null;
        $this->resetErrorBag();
    }

    public function render()
    {
        $user     = auth()->user();
        $tenantId = $user->tenant_id;
        $isSuperAdmin = $user->hasRole('super_admin');

        $tenants = $isSuperAdmin ? Tenant::where('is_active', true)->orderBy('name')->get() : collect();

        $announcements = Announcement::with(['author', 'department'])
            ->withCount('reads')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('published_at', '<=', now())
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->when($this->filterPriority, fn($q) => $q->where('priority', $this->filterPriority))
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->paginate(10);

        $departments = Department::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('is_active', true)->orderBy('name')->get();

        $detailAnnouncement = $this->detailId ? Announcement::with(['author', 'reads.user'])->find($this->detailId) : null;

        $canManage = $user->hasAnyRole(['super_admin', 'admin']);

        return view('livewire.communication.announcement-board', compact(
            'announcements', 'departments', 'detailAnnouncement', 'canManage', 'tenants'
        ))->layout('layouts.app', ['title' => 'Comunicados']);
    }
}
