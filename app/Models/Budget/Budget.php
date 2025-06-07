<?php

namespace App\Models\Budget;

use App\Models\CostCenter;
use App\Traits\TenantScoped;
use App\Traits\TenantAttributeTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\Budget\BudgetPrimaryCategory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Budget extends Model
{
  use SoftDeletes, TenantAttributeTrait, TenantScoped;

  protected $table = 'budgets';
  
  protected $fillable = [
    'cost_center_id',
    'year',
    'month',
  ];

  public function costCenter()
  {
    return $this->belongsTo(CostCenter::class);
  }

  public function primaryCategories()
  {
    return $this->hasMany(BudgetPrimaryCategory::class);
  }
}
