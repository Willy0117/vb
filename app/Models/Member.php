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
/*
    public function progress()
    {
        return $this->belongsTo(Progress::class);
    }
*/
    public function organization()
    {
        return $this->hasOne(Organization::class);
    }
}
