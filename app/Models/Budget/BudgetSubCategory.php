<?php

namespace App\Models\Budget;

use App\Models\CostCenter;
use App\Traits\TenantScoped;
use App\Traits\TenantAttributeTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\Settings\SubCategory;

class BudgetSubCategory extends Model
{
  use TenantAttributeTrait, TenantScoped;

  protected $table = 'budget_sub_categories';

  protected $fillable = [
    'budget_category_id',
    'cost_center_id',
    'sub_category_id',
    'percentage',
  ];
  
  public function getPercentageAttribute($value)
  {
    return (int) $value;
  }

  public function category()
  {
    return $this->belongsTo(BudgetCategory::class, 'budget_category_id');
  }

  public function costCenter()
  {
    return $this->belongsTo(CostCenter::class);
  }

  public function subCategory()
  {
    return $this->belongsTo(SubCategory::class, 'sub_category_id');
  }
}