<?php

namespace App\Livewire\Ats;

use App\Models\JobCandidate;
use App\Models\JobOpening;
use Livewire\Component;

class KanbanPipeline extends Component
{
    public int $openingId = 0;
    public ?JobOpening $opening = null;

    // Candidate modal
    public bool   $showModal       = false;
    public bool   $isEditing       = false;
    public ?int   $editingId       = null;
    public string $candidateName   = '';
    public string $candidateEmail  = '';
    public string $candidatePhone  = '';
    public string $candidateLinkedin = '';
    public string $candidateStatus = 'applied';
    public string $candidateNotes  = '';

    public array $stages = [
        'applied'   => 'Inscritos',
        'screening' => 'Triagem',
        'interview' => 'Entrevista',
        'technical' => 'Técnico',
        'offer'     => 'Proposta',
        'hired'     => 'Contratados',
        'rejected'  => 'Rejeitados',
    ];

    public function mount(int $opening): void
    {
        $this->openingId = $opening;
        $this->opening   = JobOpening::with('department', 'jobPosition')->findOrFail($opening);
    }

    public function moveCandidate(int $candidateId, string $newStatus): void
    {
        if (!array_key_exists($newStatus, $this->stages)) {
            return;
        }
        JobCandidate::where('id', $candidateId)
            ->where('job_opening_id', $this->openingId)
            ->update(['status' => $newStatus]);
    }

    public function openAddCandidate(string $status = 'applied'): void
    {
        $this->resetCandidateForm();
        $this->candidateStatus = $status;
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEditCandidate(int $id): void
    {
        $c = JobCandidate::findOrFail($id);
        $this->editingId        = $id;
        $this->isEditing        = true;
        $this->candidateName    = $c->name;
        $this->candidateEmail   = $c->email;
        $this->candidatePhone   = $c->phone ?? '';
        $this->candidateLinkedin = $c->linkedin_url ?? '';
        $this->candidateStatus  = $c->status;
        $this->candidateNotes   = $c->notes ?? '';
        $this->showModal        = true;
    }

    public function saveCandidate(): void
    {
        $this->validate([
            'candidateName'  => 'required|string|max:255',
            'candidateEmail' => 'required|email|max:255',
            'candidatePhone' => 'nullable|string|max:20',
            'candidateLinkedin' => 'nullable|url|max:255',
            'candidateStatus' => 'required|in:' . implode(',', array_keys($this->stages)),
            'candidateNotes' => 'nullable|string|max:2000',
        ]);

        $data = [
            'job_opening_id' => $this->openingId,
            'name'           => $this->candidateName,
            'email'          => $this->candidateEmail,
            'phone'          => $this->candidatePhone ?: null,
            'linkedin_url'   => $this->candidateLinkedin ?: null,
            'status'         => $this->candidateStatus,
            'notes'          => $this->candidateNotes ?: null,
        ];

        if ($this->isEditing) {
            JobCandidate::findOrFail($this->editingId)->update($data);
            $msg = 'Candidato atualizado.';
        } else {
            JobCandidate::create($data);
            $msg = 'Candidato adicionado.';
        }

        $this->showModal = false;
        $this->resetCandidateForm();
        $this->dispatch('toast', type: 'success', message: $msg);
    }

    public function deleteCandidate(int $id): void
    {
        JobCandidate::where('id', $id)->where('job_opening_id', $this->openingId)->delete();
        $this->dispatch('toast', type: 'success', message: 'Candidato removido.');
    }

    private function resetCandidateForm(): void
    {
        $this->editingId = null;
        $this->candidateName = $this->candidateEmail = $this->candidatePhone =
        $this->candidateLinkedin = $this->candidateNotes = '';
        $this->candidateStatus = 'applied';
        $this->resetErrorBag();
    }

    public function render()
    {
        $candidates = JobCandidate::where('job_opening_id', $this->openingId)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->groupBy('status');

        return view('livewire.ats.kanban-pipeline', [
            'candidates' => $candidates,
        ])->layout('layouts.app', ['title' => 'Pipeline — ' . ($this->opening->title ?? 'Vaga')]);
    }
}
