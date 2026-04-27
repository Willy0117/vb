<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreUser extends Model
{
    protected $fillable = [
        'email',
        'token',
        'agent',
        'expires_at',
        'verified_at',
        'agreed_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'agreed_at' => 'datetime',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        // これにより、Vueには "2026-03-12 16:42:00" という形式で渡ります
        return $date->format('Y-m-d H:i:s');
    }
    
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isVerified(): bool
    {
        return ! is_null($this->verified_at);
    }

    public function verify(): void
    {
        $this->update([
            'verified_at' => now(),
        ]);
    }

    public function isAgent(): bool
    {
        return (bool) $this->agent;
    }
    
}
