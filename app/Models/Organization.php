<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $fillable = [
        'member_id',
        'name',
        'prefix',
        'suffix',
        'registration_number',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function contacts()
    {
        return $this->hasMany(OrganizationContact::class);
    }

    public function addresses()
    {
        return $this->hasMany(OrganizationAddress::class);
    }
    
    public function documents()
    {
        return $this->hasMany(OrganizationDocument::class);
    }

    public function historyCertificate()
    {
        return $this->hasOne(OrganizationDocument::class)
            ->where('type', 'history_certificate');
    }
    /**
     * 法人正式名称
     * 例：株式会社ビジョンブリッジ
     */
    public function getFullNameAttribute(): string
    {
        return trim(
            ($this->prefix ?? '')
            . $this->name
            . ($this->suffix ?? '')
        );
    }
    
}
