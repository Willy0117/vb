<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationBankAccount extends Model
{
    protected $table = 'application_bank_accounts';

    protected $fillable = [
        'member_id',
        'bank_type',
        'bank_name',
        'bank_code',
        'branch_name',
        'branch_code',
        'account_type',
        'account_no',
        'account_kana',
        'account_name',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
