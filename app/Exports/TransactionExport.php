<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransactionExport implements FromArray, WithHeadings
{
  protected array $transactions;

  public function __construct(array $transactions)
  {
    $this->transactions = $transactions;
  }

  public function array(): array
  {
    return $this->transactions;
  }

  public function headings(): array
  {
    if (empty($this->transactions)) {
      return [];
    }

    return array_keys($this->transactions[0]);
  }
}
