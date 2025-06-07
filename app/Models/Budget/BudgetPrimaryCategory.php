<?php

namespace App\Models\Budget;

use App\Models\CostCenter;
use App\Traits\TenantScoped;
use App\Traits\TenantAttributeTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\Settings\PrimaryCategory;

class BudgetPrimaryCategory extends Model
{
  use TenantAttributeTrait, TenantScoped;

  protected $table = 'budget_primary_categories';
  
  protected $fillable = [
    'budget_id',
    'cost_center_id',
    'primary_category_id',
    'percentage',
  ];
  
  public function getPercentageAttribute($value)
  {
    return (int) $value;
  }

  public function budget()
  {
    return $this->belongsTo(Budget::class);
  }

  public function costCenter()
  {
    return $this->belongsTo(CostCenter::class);
  }

  public function primaryCategory()
  {
    return $this->belongsTo(PrimaryCategory::class);
  }

  public function secondaryCategories()
  {
    return $this->hasMany(BudgetSecondaryCategory::class);
  }
}
