<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    const TYPE_WEB = 'web';
    const TYPE_PAPER = 'paper';

    protected $fillable = [
        'type',
        'last_name',
        'first_name',
        'last_name_kana',
        'first_name_kana',
        'agree',
        'affiliate',
        'agreed_at',
        'status_id',
        'progress_id',
        'agent',
        'joined_at',
        'canceled_at',
        'withdrawn_at',
        'region_id',
        'number',
        'aplus_customer_no',
        'jac_certification_no', 
        'updated_by',
        'desired_join_month',
        'application_type',
    ];

    protected $casts = [
        'agent' => 'boolean',
        'agree' => 'boolean',
        'affiliate' => 'boolean',
        'agreed_at' => 'datetime',
        'joined_at' => 'datetime',
        'canceled_at' => 'datetime',
        'withdrawn_at' => 'datetime',
//        'desired_join_month' => 'date',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        // これにより、Vueには "2026-03-12 16:42:00" という形式で渡ります
        return $date->format('Y-m-d H:i:s');
    }
    
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function progress()
    {
        return $this->belongsTo(Progress::class, 'progress_id'); 
    }

    public function organizations()
    {
        return $this->hasMany(Organization::class);
    }
/*
    public function organization()
    {
        return $this->hasOne(Organization::class)->latestOfMany();
    }
*/
    public function organization()
    {
        return $this->hasOne(Organization::class)
            ->where('type', 1);
    }
    
    public function corpOrg()
    {
        return $this->hasOne(Organization::class)->where('type', 1);
    }

    public function mailOrg()
    {
        return $this->hasOne(Organization::class)->where('type', 2);
    }
    public function agentOrg()
    {
        return $this->hasOne(Organization::class)->where('type', 3);
    }


    public function applicationOrganizations()
    {
        return $this->hasMany(ApplicationOrganization::class);
    }
/*
    public function applicationOrganization()
    {
        return $this->hasOne(ApplicationOrganization::class)->latestOfMany();
    }
*/
     // 複数取得用
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    // 1件取得用（最新の1件を取得する場合）
    public function invoice()
    {
        // latestOfMany() を付けることで、常に「最新の1件」を返す1対1のリレーションになります
        return $this->hasOne(Invoice::class)->latestOfMany();
    }

    public function getFullNameAttribute()
    {
        return trim($this->last_name . ' ' . $this->first_name);
    }
    
    public function bankAccount()
    {
        return $this->hasOne(BankAccount::class);
    }
    
    public function applicationBankAccount()
    {
        return $this->hasOne(ApplicationBankAccount::class);
    }


    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    /* =====================
     |  表示用ラベル
     * ===================== */

    // 法人 / 個人事業主
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'corporation' => '法人',
            'solo' => '個人事業主',
            default => '-',
        };
    }

    // 代理人申請 / 本人申請
    public function getAgentLabelAttribute(): string
    {
        return $this->agent ? '代理人申請' : '本人申請';
    }

    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function statusHistories()
    {
        return $this->hasMany(MemberStatusHistory::class);
    }

    public function progressHistories()
    {
        return $this->hasMany(MemberProgressHistory::class);
    }

    public function getDesiredJoinMonthYmAttribute()
    {
        return optional($this->desired_join_month)->format('Y-m');
    }

}
