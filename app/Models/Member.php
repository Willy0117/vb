<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        'agree',
        'affiliate',
        'agreed_at',
        'verified_at',
        'status_id',
        'progress_id',
    ];

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function progress()
    {
        return $this->belongsTo(Progress::class, 'progress_id'); 
    }

    public function organization()
    {
        return $this->hasOne(Organization::class);
    }
    
    public function organizations()
    {
        return $this->hasMany(Organization::class);
    }

    public function getFullNameAttribute()
    {
        return trim($this->last_name . ' ' . $this->first_name);
    }    
}
