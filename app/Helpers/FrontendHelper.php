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
}
