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

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
    //
}
