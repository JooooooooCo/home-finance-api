<?php

namespace App\Models\Settings;

use App\Models\CostCenter;
use App\Traits\TenantScoped;
use App\Models\CashFlow\Transaction;
use App\Traits\TenantAttributeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Classification extends Model
{
  use SoftDeletes, TenantAttributeTrait, TenantScoped;

  protected $table = 'classifications';

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
    return $this->hasMany(Transaction::class, 'classification_id');
  }
}