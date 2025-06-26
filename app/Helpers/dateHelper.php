<?php

namespace App\Helpers;

class dateHelper
{
  public static function getMonthInitialEndDate(int $year, int $month)
  {
    $startDate = sprintf('%04d-%02d-01', $year, $month);

    return [
      'startDate' => $startDate,
      'endDate' => date('Y-m-t', strtotime($startDate)),
    ];
  }
}
