<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'company_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ─── Relacionamentos ───

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function createdCompanies(): HasMany
    {
        return $this->hasMany(Company::class, 'user_id');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    // ─── Helpers de Perfil ───

    public function isAdminGlobal(): bool
    {
        return $this->role === 'admin_global';
    }

    public function isAdminCliente(): bool
    {
        return $this->role === 'admin_cliente';
    }

    /**
     * Verifica se o usuário pode acessar dados de uma empresa específica.
     */
    public function canAccessCompany(int $companyId): bool
    {
        if ($this->isAdminGlobal()) {
            return true;
        }

        // Tem acesso se for sua empresa principal ou se ele a criou
        return $this->company_id === $companyId || $this->createdCompanies()->where('id', $companyId)->exists();
    }

    /**
     * Retorna o nome legível do perfil.
     */
    public function getRoleNameAttribute(): string
    {
        return match ($this->role) {
            'admin_global' => 'Admin Global',
            'admin_cliente' => 'Usuário',
            default => 'Desconhecido',
        };
    }
}
