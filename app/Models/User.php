<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Panel;
use App\Models\Traits\HasPermissions;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasPermissions;

    protected $with = ['currentIssuer'];

    protected $guarded = ['id'];

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
            'password' => 'hashed',
        ];
    }

    public function currentIssuer()
    {
        return $this->belongsTo(Issuer::class, 'issuer_id');
    }

    public function issuers()
    {
        return $this->belongsToMany(Issuer::class, 'users_issuers_permissions', 'user_id', 'issuer_id')
            ->using(IssuerUserPermission::class)
            ->withPivot(['expires_at', 'active'])
            ->withTimestamps();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
