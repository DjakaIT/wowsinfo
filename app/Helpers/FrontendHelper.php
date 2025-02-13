<?php

namespace App\Helpers;

class FrontendHelper
{
    public static function getWN8Color($value)
    {
        if ($value >= 0 && $value < 750) {
          return 'colorRatePR_1';
        } elseif ($value >= 750 && $value <= 1100) {
          return 'colorRatePR_2';
        } elseif ($value >= 1100 && $value <= 1350) {
          return 'colorRatePR_3';
        } elseif ($value >= 1350 && $value <= 1550) {
          return 'colorRatePR_4';
        } elseif ($value >= 1550 && $value <= 1750) {
          return 'colorRatePR_5';
        } elseif ($value >= 1750 && $value <= 2100) {
          return 'colorRatePR_6';
        } elseif ($value >= 2100 && $value <= 2450) {
          return 'colorRatePR_7';
        } else {
            return 'colorRatePR_8';
        }
    }

    public static function getWinColor($value)
    {
        if ($value < 45) {
          return 'colorRateWN8_1';
        } elseif ($value < 47) {
          return 'colorRateWN8_2';
        } elseif ($value < 49) {
          return 'colorRateWN8_3';
        } elseif ($value < 50) {
          return 'colorRateWN8_4';
        } elseif ($value < 56) {
          return 'colorRateWN8_5';
        } elseif ($value < 60) {
          return 'colorRateWN8_6';
        } elseif ($value < 65) {
          return 'colorRateWN8_7';
        } else {
          return 'colorRateWN8_8';
        }
    }

    public static function getFlags($value)
    {
        $nationImages = [
            'usa' => 'https://wiki.wgcdn.co/images/f/f2/Wows_flag_USA.png',
            'pan_asia' => 'https://wiki.wgcdn.co/images/3/33/Wows_flag_Pan_Asia.png',
            'ussr' => 'https://wiki.wgcdn.co/images/0/04/Wows_flag_Russian_Empire_and_USSR.png',
            'europe' => 'https://wiki.wgcdn.co/images/5/52/Wows_flag_Europe.png',
            'japan' => 'https://wiki.wgcdn.co/images/5/5b/Wows_flag_Japan.png',
            'uk' => 'https://wiki.wgcdn.co/images/3/34/Wows_flag_UK.png',
            'germany' => 'https://wiki.wgcdn.co/images/6/6b/Wows_flag_Germany.png',
            'netherlands' => 'https://wiki.wgcdn.co/images/c/c8/Wows_flag_Netherlands.png',
            'italy' => 'https://wiki.wgcdn.co/images/d/d1/Wows_flag_Italy.png',
            'france' => 'https://wiki.wgcdn.co/images/7/71/Wows_flag_France.png',
            'commonwealth' => 'https://wiki.wgcdn.co/images/9/9a/Wows_flag_Commonwealth.png',
            'spain' => 'https://wiki.wgcdn.co/images/thumb/e/ea/Flag_of_Spain_%28state%29.jpg/80px-Flag_of_Spain_%28state%29.jpg',
            'pan_america' => 'https://wiki.wgcdn.co/images/9/9e/Wows_flag_Pan_America.png',
        ];

        return $nationImages[$value] ?? null;
    }
}
