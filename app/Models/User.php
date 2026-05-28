<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable // implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function hasRole(string ...$roles): bool
    {
        return $this->role !== null && in_array($this->role->name, $roles, true);
    }

    public function isAdministrator(): bool
    {
        return $this->hasRole(Role::Admin);
    }

    public function homeRouteName(): string
    {
        return match ($this->role?->name) {
            Role::Admin => 'dashboard',
            Role::Seller => 'sales.dashboard',
            Role::Warehouse => 'inventory.dashboard',
            default => 'dashboard',
        };
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'user_id');
    }

    public function devoluciones(): HasMany
    {
        return $this->hasMany(Devolucion::class, 'user_id');
    }

    public function cajasApertura(): HasMany
    {
        return $this->hasMany(Caja::class, 'user_id_open');
    }

    public function cajasCierre(): HasMany
    {
        return $this->hasMany(Caja::class, 'user_id_close');
    }
}
