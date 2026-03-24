<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'message',
        'data',
    ];

    // data を配列として自動変換
    protected $casts = [
        'data' => 'array',
    ];
}
