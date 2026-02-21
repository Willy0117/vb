<?php 
namespace App\Enums;

enum MemberStatus: int
{
    case APPLYING  = 1;
    case JOINED    = 2;
    case WITHDRAWN = 3;
    case CANCELED  = 4;

    public function label(): string
    {
        return match ($this) {
            self::APPLYING  => '申請中',
            self::JOINED    => '入会',
            self::WITHDRAWN => '退会',
            self::CANCELED  => '申込キャンセル',
        };
    }
}
