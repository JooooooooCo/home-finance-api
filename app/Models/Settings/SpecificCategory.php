<?php

namespace App\Models\Settings;

use App\Models\CostCenter;
use App\Traits\TenantScoped;
use App\Models\CashFlow\Transaction;
use App\Traits\TenantAttributeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpecificCategory extends Model
{
  use SoftDeletes, TenantAttributeTrait, TenantScoped;

  protected $table = 'specific_categories';

  protected $fillable = [
    'name',
    'secondary_category_id',
    'cost_center_id',
  ];

  public function costCenter()
  {
    return $this->belongsTo(CostCenter::class);
  }

  public function secondaryCategory()
  {
    return $this->belongsTo(SecondaryCategory::class);
  }

  public function transactions()
  {
    return $this->hasMany(Transaction::class);
  }
}
