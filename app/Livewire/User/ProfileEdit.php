<?php

namespace App\Livewire\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProfileEdit extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';
    public $avatar = null;

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
    }

    public function updateProfile(): void
    {
        $user = Auth::user();

        $this->validate([
            'name'   => 'required|string|max:255',
            'email'  => 'required|email|max:255|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|max:2048',
        ]);

        $data = ['name' => $this->name, 'email' => $this->email];

        if ($this->avatar) {
            $data['avatar_path'] = $this->avatar->store('avatars', 'public');
        }

        $user->update($data);
        $this->avatar = null;

        $this->dispatch('toast', type: 'success', message: 'Perfil atualizado com sucesso!');
    }

    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($this->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'A senha atual está incorreta.',
            ]);
        }

        $user->update(['password' => Hash::make($this->password)]);

        $this->current_password = '';
        $this->password = '';
        $this->password_confirmation = '';

        $this->dispatch('toast', type: 'success', message: 'Senha alterada com sucesso!');
    }

    public function render()
    {
        return view('livewire.user.profile-edit')
            ->layout('layouts.app', ['title' => 'Meu Perfil']);
    }
}
