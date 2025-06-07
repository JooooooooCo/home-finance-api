<?php

namespace App\Models\Budget;

use App\Models\CostCenter;
use App\Traits\TenantScoped;
use App\Traits\TenantAttributeTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\Settings\SecondaryCategory;

class BudgetSecondaryCategory extends Model
{
  use TenantAttributeTrait, TenantScoped;

  protected $table = 'budget_secondary_categories';

  protected $fillable = [
    'budget_primary_category_id',
    'cost_center_id',
    'secondary_category_id',
    'percentage',
  ];
  
  public function getPercentageAttribute($value)
  {
    return (int) $value;
  }

  public function primaryCategory()
  {
    return $this->belongsTo(BudgetPrimaryCategory::class, 'budget_primary_category_id');
  }

  public function costCenter()
  {
    return $this->belongsTo(CostCenter::class);
  }

  public function secondaryCategory()
  {
    return $this->belongsTo(SecondaryCategory::class);
  }

  public function specificCategories()
  {
    return $this->hasMany(BudgetSpecificCategory::class);
  }
}
