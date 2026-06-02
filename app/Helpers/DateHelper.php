<?php

namespace App\Helpers;

use IntlDateFormatter;
use Carbon\CarbonInterface;

class DateHelper
{
    
    public static function withWareki(\Carbon\Carbon $date, bool $monthOnly = false): string
    {
        $formatter = new \IntlDateFormatter(
            'ja_JP@calendar=japanese',
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            'Asia/Tokyo',
            \IntlDateFormatter::TRADITIONAL,
            $monthOnly ? 'Gy年M月' : 'Gy年M月d日'
        );

        $wareki = $formatter->format($date);
        $seireki = $monthOnly ? $date->format('Y/m') : $date->format('Y/m/d');

        return "{$seireki} ({$wareki}）";
    }

}
