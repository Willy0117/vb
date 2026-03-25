<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberProgressHistory extends Model
{
    protected $fillable = [
        'member_id',
        'progress_id',
        'changed_by',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
    
    //
}
