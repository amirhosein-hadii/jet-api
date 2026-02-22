<?php

use Carbon\Carbon;
use Morilog\Jalali\CalendarUtils;
use Morilog\Jalali\Jalalian;

function convertToEnglishNums($number)
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic = ['٩', '٨', '٧', '٦', '٥', '٤', '٣', '٢', '١','٠'];

        $num = range(0, 9);
        $convertedPersianNums = str_replace($persian, $num, $number);
        $englishNumbersOnly   = str_replace($arabic, $num, $convertedPersianNums);

        return $englishNumbersOnly;
    }

    function convertToValidString($string)
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic = ['٩', '٨', '٧', '٦', '٥', '٤', '٣', '٢', '١','٠'];
        $num = range(0, 9);

        $convertedPersianNums = str_replace($persian, $num, $string);
        $englishNumbersOnly   = str_replace($arabic, $num, $convertedPersianNums);

        $string = str_replace("ي", "ی", $englishNumbersOnly);
        $string = str_replace("ك", "ک", $string);

        return $string;
    }

    function riyalToToman($amount)
    {
        return $amount / 10;
    }

    function tomanToriyal($amount)
    {
        return $amount * 10;
    }

    function percentage($number)
    {
        return $number / 100;
    }

    function convertToJalali($dateTime)
    {
        $dateTime = \Carbon\Carbon::parse($dateTime)->format('Y-m-d H:i:s');

        if (!is_null($dateTime)) {
            $gregorianDateTime = explode(' ', $dateTime);
            $gregorianDate = $gregorianDateTime[0];
            $gregorianTime = $gregorianDateTime[1] ?? null;
            $explodedDate = explode('-', $gregorianDate);

            $updatedAt = \Morilog\Jalali\CalendarUtils::toJalali($explodedDate[0], $explodedDate[1], $explodedDate[2]);
            $jalaliDate = setJalalianTimeFormat($updatedAt[0], $updatedAt[1],$updatedAt[2]). ' ' . $gregorianTime;
        } else
            $jalaliDate = null;

        return $jalaliDate;
    }

    function setJalalianTimeFormat($year, $month, $day)
    {
        if($month < 10 && strlen($month) == 1)
            $month = '0'.$month;
        if($day < 10 && strlen($day) == 1)
            $day = '0'.$day;
        return $year.'-'.$month.'-'.$day;
    }

    function convertToBeginOfDate($datetime)
    {
        return \Carbon\Carbon::parse($datetime)->format('Y-m-d') . ' 00:00:00';
    }

    function diffInTwoDates($dateFrom, $dateTo)
    {
        return Carbon::parse($dateTo)->diffInDays(Carbon::parse($dateFrom));
    }

    function dashedJalalianToDashedGregorian($date)
    {
        $jalalian = Carbon::parse($date);
        $gregorian =  CalendarUtils::toGregorian($jalalian->format('Y'), $jalalian->format('m'), $jalalian->format('d'));
        $gregorian = setJalalianTimeFormat($gregorian[0], $gregorian[1], $gregorian[2]);
        return $gregorian;
    }

    function convertToJalaliReverse($dateTime)
    {
        if (!is_null($dateTime)) {
            $gregorianDateTime = explode(' ', $dateTime);
            $gregorianDate = $gregorianDateTime[0];
            $gregorianTime = $gregorianDateTime[1] ?? null;
            $explodedDate = explode('-', $gregorianDate);

            $updatedAt = \Morilog\Jalali\CalendarUtils::toJalali($explodedDate[0], $explodedDate[1], $explodedDate[2]);
            $jalaliDate = $updatedAt[2] . '-' . $updatedAt[1] . '-' . $updatedAt[0];
        } else
            $jalaliDate = null;

        return $jalaliDate;
    }


    function convertReelToDashedJalalian(string $val)
    {
        $date = "";
        $year = $val[0] . $val[1] . $val[2] . $val[3];
        $month = $val[4] . $val[5];
        $day = $val[6] . $val[7];

        $date .= $year . "-" . $month . "-" . $day;
        return $date;
    }

    function convertReelToDashedJalalianReverse(string $val)
    {
        $date = "";
        $year = $val[0] . $val[1] . $val[2] . $val[3];
        $month = $val[4] . $val[5];
        $day = $val[6] . $val[7];

        $date .= $day . "-" . $month . "-" . $year;
        return $date;
    }

    function convertDashedToReelJalalian(string $val)
    {
        return str_replace('-', '', $val);
    }

    function todayInJalali()
    {
        return \Morilog\Jalali\Jalalian::forge(Carbon::now())->format('Ymd');
    }

    function subdayInJalali($number)
    {
        return \Morilog\Jalali\Jalalian::forge(Carbon::now())->subDays($number)->format('Ymd');
    }

    function convertReelJalalianToDashedGregorian($jalalian)
    {
        $gregorian = CalendarUtils::toGregorian(substr($jalalian,0,4), substr($jalalian,4,2), substr($jalalian,6,2));
        return setJalalianTimeFormat($gregorian[0], $gregorian[1], $gregorian[2]);
    }

    function haveSameValues(array $arrayA, array $arrayB) {
        return empty(array_diff($arrayA, $arrayB)) && empty(array_diff($arrayB, $arrayA));
    }

    function jalalianAddDays(int $days) {
        return Jalalian::forge(Carbon::now()->addDays($days))->format('Ymd');
    }

    function convertToDashedJalalian($date)
    {
        return \Morilog\Jalali\Jalalian::forge($date)->format('Y-m-d');
    }
