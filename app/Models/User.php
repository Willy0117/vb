<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasProfilePhoto, Notifiable, TwoFactorAuthenticatable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $appends = [
        'profile_photo_url',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    
    public function member()
    {
        return $this->hasOne(Member::class);
    }
    // Tenant リレーション
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * ログインユーザーの tenant_id でフィルターしたロールを取得
     */
    public function tenantRoles()
    {
        if ($this->isSuperAdmin()) {
            return $this->roles->get();
        }
        return $this->roles()->where('roles.tenant_id', $this->tenant_id); 
    }

    /**
     * ログインユーザーの tenant_id でフィルターした権限を取得
     */
    public function tenantPermissions()
    {
        // SuperAdminなら全権限
        if ($this->isSuperAdmin()) {
            return $this->getAllPermissions();
        }

        // ユーザーに紐付く権限のうち、現在の tenant_id と一致するものだけを取得
        return $this->permissions()
            ->where('permissions.tenant_id', $this->tenant_id);
    }

    public function isSuperAdmin()
    {
        return $this->hasRole('super_admin');
    }
}

