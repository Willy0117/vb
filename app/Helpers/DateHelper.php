<?php

namespace App\Helpers;

use IntlDateFormatter;
use Carbon\CarbonInterface;

class DateHelper
{
    public static function withWareki(\Carbon\Carbon $date): string
    {
        $formatter = new \IntlDateFormatter(
            'ja_JP@calendar=japanese',
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            'Asia/Tokyo',
            \IntlDateFormatter::TRADITIONAL, // ここを指定するとより確実
            'Gy年M月d日'
        );

        // ★ タイムスタンプではなく、Carbonオブジェクトをそのまま渡す
        $wareki = $formatter->format($date);

        $seireki = $date->format('Y/m/d');

        return "{$seireki} ({$wareki}）";
    }

}
