<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = [
        'member_id',
        'last_name',
        'last_name_kana',
        'first_name',
        'first_name_kana',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function getFullNameAttribute(): string
    {
        return $this->last_name . ' ' . $this->first_name;
    }

    public function getFullNameKanaAttribute(): string
    {
        return $this->last_name_kana . ' ' . $this->first_name_kana;
    }
}
