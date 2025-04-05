<?php

namespace App\Models\Settings;

use App\Models\CostCenter;
use App\Models\CashFlow\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrimaryCategory extends Model
{
  use SoftDeletes;

  protected $table = 'primary_categories';

  protected $fillable = [
    'name',
    'cost_center_id',
  ];

  public function costCenter()
  {
    return $this->belongsTo(CostCenter::class);
  }

  public function transactions()
  {
    return $this->hasMany(Transaction::class);
  }
}
