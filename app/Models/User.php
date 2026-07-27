<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
    
    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isVisualizador(): bool { return $this->role === 'visualizador'; }
    public function isComercial(): bool { return $this->role === 'comercial'; }
    public function isLogistica(): bool { return $this->role === 'logistica'; }
    public function isTecnico(): bool { return $this->role === 'tecnico'; }
}
