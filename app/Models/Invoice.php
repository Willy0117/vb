<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'member_id',
        'invoice_no',
        'issued_at',
        'due_date',
        'amount',
        'status',
        'paid_at',
        'note',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'due_date' => 'datetime',
        'paid_at' => 'datetime',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        // これにより、Vueには "2026-03-12 16:42:00" という形式で渡ります
        return $date->format('Y-m-d H:i:s');
    }
    
    public function member()
    {
        return $this->belongsTo(Member::class);
    }
    //
}
